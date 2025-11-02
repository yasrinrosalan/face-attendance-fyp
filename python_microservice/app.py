# path: python_microservice/app.py

import os
import base64
import re
import glob
import shutil
from flask import Flask, request, jsonify
from deepface import DeepFace

# Initialize the Flask application
app = Flask(__name__)

# --- Configuration ---
# Path to the folder where we will store face data
# This folder will be created automatically
DB_PATH = "face_data"
# The model we'll use. VGG-Face is a good balance of speed and accuracy.
MODEL_NAME = "VGG-Face"
# The distance metric to use
DISTANCE_METRIC = "cosine"
# File name for the temporary file used for verification
VERIFY_TEMP_FILE = "temp_verify.jpg"
# The file deepface uses to cache representations. We will delete this
# to force it to re-index when a new face is enrolled.
PICKLE_FILE = os.path.join(DB_PATH, "representations_vgg_face.pkl")


# --- Helper Function ---
def decode_base64_image(data_url, output_path):
    """
    Decodes a Base64 data URL (e.g., "data:image/jpeg;base64,...")
    and saves it as an image file.
    """
    try:
        # Remove the "data:image/jpeg;base64," part
        img_str = re.search(r'base64,(.*)', data_url).group(1)
        # Decode the base64 string
        img_data = base64.b64decode(img_str)
        # Write the image data to a file
        with open(output_path, 'wb') as f:
            f.write(img_data)
        return True
    except Exception as e:
        print(f"Error decoding base64 image: {e}")
        return False

# --- API Endpoints ---

@app.route('/enroll', methods=['POST'])
def enroll():
    """
    Endpoint to enroll a new face.
    Receives:
    {
        "student_id": "123",
        "image_base64": "data:image/jpeg;base64,..."
    }
    """
    try:
        data = request.get_json()
        student_id = data.get('student_id')
        image_base64 = data.get('image_base64')

        if not student_id or not image_base64:
            return jsonify({"status": "error", "message": "Missing student_id or image_base64"}), 400

        # --- File & Directory Management ---
        
        # Create a specific folder for this student
        # e.g., "face_data/student_123"
        student_dir = os.path.join(DB_PATH, f"student_{student_id}")
        
        # If the directory already exists, clear it out to ensure only one enrollment image
        if os.path.exists(student_dir):
            shutil.rmtree(student_dir)
        
        # Create the new, empty directory
        os.makedirs(student_dir, exist_ok=True)
        
        # Define the path to save the image
        img_path = os.path.join(student_dir, "face.jpg")

        # 1. Decode and save the enrollment image
        if not decode_base64_image(image_base64, img_path):
            return jsonify({"status": "error", "message": "Failed to decode image"}), 500

        # 2. IMPORTANT: Force re-indexing
        # DeepFace creates a .pkl file to cache face representations.
        # By deleting it, we force DeepFace to re-scan the entire
        # face_data directory (including our new student) on the next
        # 'verify' call. This is simple and effective for an FYP.
        if os.path.exists(PICKLE_FILE):
            os.remove(PICKLE_FILE)

        print(f"Successfully enrolled student {student_id}")
        return jsonify({
            "status": "success",
            "message": "Face enrolled successfully",
            "path": student_dir # Send the path back to Laravel
        })

    except Exception as e:
        print(f"Error in /enroll: {e}")
        return jsonify({"status": "error", "message": str(e)}), 500


@app.route('/verify', methods=['POST'])
def verify():
    """
    Endpoint to verify a face.
    Receives:
    {
        "image_base64": "data:image/jpeg;base64,..."
    }
    """
    try:
        data = request.get_json()
        image_base64 = data.get('image_base64')

        if not image_base64:
            return jsonify({"status": "error", "message": "Missing image_base64"}), 400

        # 1. Decode and save the verification image
        if not decode_base64_image(image_base64, VERIFY_TEMP_FILE):
            return jsonify({"status": "error", "message": "Failed to decode image"}), 500

        # 2. Call DeepFace.find()
        # This will search for the face in VERIFY_TEMP_FILE against all
        # faces in the DB_PATH directory.
        # If the .pkl file is missing (from /enroll), it will
        # automatically re-generate it.
        try:
            # Note: enforce_detection=False allows it to proceed even if alignment is tricky
            # You can set this to True for stricter matching
            dfs = DeepFace.find(
                img_path=VERIFY_TEMP_FILE,
                db_path=DB_PATH,
                model_name=MODEL_NAME,
                distance_metric=DISTANCE_METRIC,
                enforce_detection=False 
            )
            
            # dfs is a list of dataframes. We only care about the first (and only) result.
            if not dfs or dfs[0].empty:
                print("Verification failed: No matching face found in database.")
                return jsonify({"status": "fail", "message": "No matching face found"})
                
            # Get the first dataframe
            df = dfs[0]
            
            # Find the best match (lowest distance)
            if "distance" in df.columns:
                best_match = df.iloc[df['distance'].idxmin()]
                identity_path = best_match['identity'] # e.g., "face_data/student_123/face.jpg"
                distance = best_match['distance']

                # Extract the student ID from the path
                # "face_data/student_123/face.jpg" -> "123"
                match = re.search(r'student_(\d+)', identity_path)
                if match:
                    student_id = int(match.group(1))
                    print(f"Verification success: Matched student {student_id} with distance {distance}")
                    return jsonify({
                        "status": "success",
                        "student_id": student_id,
                        "confidence": 1 - distance # Cosine distance is 0-1 (0=identical), so 1-dist = confidence
                    })
            
            print("Verification failed: DataFrame was empty or missing 'distance' column.")
            return jsonify({"status": "fail", "message": "Verification failed"})

        except ValueError as ve:
            # This often happens if no face is detected in the input image
            print(f"Verification error (likely no face detected): {ve}")
            return jsonify({"status": "fail", "message": "No face detected in the image. Please try again."})
        finally:
            # 3. Clean up the temporary verification file
            if os.path.exists(VERIFY_TEMP_FILE):
                os.remove(VERIFY_TEMP_FILE)

    except Exception as e:
        print(f"Error in /verify: {e}")
        return jsonify({"status": "error", "message": str(e)}), 500


# --- Main entry point ---
if __name__ == '__main__':
    # Create the face database directory if it doesn't exist
    os.makedirs(DB_PATH, exist_ok=True)
    # Run the Flask app
    # host='0.0.0.0' makes it accessible from other devices on your network
    # (like your main Laravel app)
    app.run(host='0.0.0.0', port=5000, debug=True)