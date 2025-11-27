import os
import base64
import json
import cv2 # OpenCV for image analysis
import numpy as np # Number crunching
from flask import Flask, request, jsonify
from deepface import DeepFace
from scipy.spatial.distance import cosine

app = Flask(__name__)

# --- Configuration ---
# VGG-Face is good, but Facenet512 is often more accurate for verification
MODEL_NAME = "Facenet512"
# MTCNN is slower but much more robust at finding faces than the default detector
DETECTOR_BACKEND = "mtcnn"
VERIFY_TEMP_FILE = "temp_verify.jpg"
ENCODINGS_FILE = "student_encodings.json"
# Threshold adjusted for Facenet512. Lower means stricter matching.
VERIFICATION_THRESHOLD = 0.40 

# --- NEW: LIVENESS CONFIGURATION ---
# Texture threshold: Below this, image is too smooth (like a screen/photo). 
LIVENESS_TEXTURE_THRESHOLD = 60.0 

# Glare threshold: Max ratio of pure white pixels allowed. 0.01 = 1%.
LIVENESS_GLARE_THRESHOLD = 0.01 


# --- Helper Function: Liveness Detection (Anti-Spoofing) ---
def check_liveness_potential(image_path):
    """
    Performs heuristic checks to detect potential photo/screen spoofing attacks.
    Returns: (bool, string) -> (Passed Liveness?, Reason if failed)
    """
    img = cv2.imread(image_path)
    if img is None:
        return False, "Could not read image file for liveness check."

    # Convert to grayscale for analysis
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # --- Check 1: Texture/Blur Analysis (Laplacian Variance) ---
    texture_score = cv2.Laplacian(gray, cv2.CV_64F).var()
    
    print(f"[Liveness Debug] Texture Score: {texture_score:.2f} (Threshold: {LIVENESS_TEXTURE_THRESHOLD})")

    if texture_score < LIVENESS_TEXTURE_THRESHOLD:
        return False, "Liveness check failed: Image is too smooth or blurry. Often indicates a photo attack or poor camera."


    # --- Check 2: Glare/Lighting Analysis (Histogram) ---
    hist = cv2.calcHist([gray], [0], None, [256], [0, 256])
    brightest_pixels_count = hist[255][0]
    total_pixels = gray.shape[0] * gray.shape[1]
    glare_ratio = brightest_pixels_count / total_pixels

    print(f"[Liveness Debug] Glare Ratio: {glare_ratio:.4f} (Threshold: {LIVENESS_GLARE_THRESHOLD})")

    if glare_ratio > LIVENESS_GLARE_THRESHOLD:
        return False, "Liveness check failed: Abnormal lighting/glare detected. Often indicates a screen reflection."

    # If both checks pass
    return True, "Liveness checks passed."


# --- Helper Function: Decode Base64 (FIXED) ---
def decode_base64_image(data_url, output_path):
    try:
        # FIX: Handle both full Data URLs and raw Base64 strings
        if ',' in data_url:
            # Split on the comma and take the second part (the actual data)
            img_str = data_url.split(',')[1]
        else:
            # It's already raw base64
            img_str = data_url

        img_data = base64.b64decode(img_str)
        with open(output_path, 'wb') as f:
            f.write(img_data)
        return True
    except Exception as e:
        print(f"Error decoding base64 image: {e}")
        return False

# --- Helper Functions: JSON management (Unchanged) ---
def load_encodings():
    if os.path.exists(ENCODINGS_FILE):
        with open(ENCODINGS_FILE, 'r') as f:
            try:
                return json.load(f)
            except json.JSONDecodeError:
                return {}
    return {}

def save_encodings(encodings):
    with open(ENCODINGS_FILE, 'w') as f:
        json.dump(encodings, f)


# ===========================
# API ENDPOINTS
# ===========================

# --- /enroll Endpoint ---
@app.route('/enroll', methods=['POST'])
def enroll():
    try:
        data = request.get_json()
        student_id = str(data.get('student_id'))
        image_base64 = data.get('image_base64')

        if not student_id or not image_base64:
            return jsonify({"status": "error", "message": "Missing data"}), 400

        # 1. Decode image to temp file
        if not decode_base64_image(image_base64, VERIFY_TEMP_FILE):
            return jsonify({"status": "error", "message": "Failed to decode image"}), 500

        try:
            # --- STEP 2: PERFORM LIVENESS DETECTION ---
            is_live, liveness_msg = check_liveness_potential(VERIFY_TEMP_FILE)
            
            if not is_live:
                print(f"Spoof attempt detected during enroll: {liveness_msg}")
                return jsonify({"status": "error", "message": liveness_msg}), 400
            # ------------------------------------------


            # 3. Generate embedding if liveness passed
            embedding_obj = DeepFace.represent(
                img_path=VERIFY_TEMP_FILE,
                model_name=MODEL_NAME,
                detector_backend=DETECTOR_BACKEND, 
                enforce_detection=True
            )
            encoding = embedding_obj[0]["embedding"]
        
        except ValueError as ve:
            return jsonify({"status": "error", "message": "No face detected by AI model. Please ensure face is centered."}), 400
        except Exception as e:
             print(f"DeepFace Error: {e}")
             return jsonify({"status": "error", "message": "Error processing face data."}), 500
        finally:
            if os.path.exists(VERIFY_TEMP_FILE):
                os.remove(VERIFY_TEMP_FILE)

        # 4. Save to JSON
        encodings = load_encodings()
        encodings[student_id] = encoding
        save_encodings(encodings)

        print(f"Successfully enrolled student {student_id}")
        return jsonify({ "status": "success", "message": "Face enrolled successfully!" })

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


# --- /verify Endpoint ---
@app.route('/verify', methods=['POST'])
def verify():
    try:
        data = request.get_json()
        image_base64 = data.get('image_base64')

        if not image_base64:
            return jsonify({"status": "error", "message": "Missing image_base64"}), 400

        # 1. Decode
        if not decode_base64_image(image_base64, VERIFY_TEMP_FILE):
            return jsonify({"status": "error", "message": "Failed to decode image"}), 500

        try:
            # --- STEP 2: PERFORM LIVENESS DETECTION ---
            is_live, liveness_msg = check_liveness_potential(VERIFY_TEMP_FILE)
            
            if not is_live:
                print(f"[Verify Debug] Liveness failed: {liveness_msg}")
                return jsonify({"status": "fail", "message": liveness_msg}), 200
            # ------------------------------------------

            # 3. Generate embedding if liveness passed
            embedding_obj = DeepFace.represent(
                img_path=VERIFY_TEMP_FILE,
                model_name=MODEL_NAME,
                detector_backend=DETECTOR_BACKEND, 
                enforce_detection=True
            )
            new_encoding = embedding_obj[0]["embedding"]
            print("[Verify Debug] Face detected and embedding generated successfully.")

        except ValueError as ve:
            print(f"[Verify Debug] Face detection failed: {ve}")
            return jsonify({"status": "fail", "message": "No face detected."}), 200
        finally:
            if os.path.exists(VERIFY_TEMP_FILE):
                os.remove(VERIFY_TEMP_FILE)

        # 4. Match against database
        encodings = load_encodings()
        if not encodings:
            print("[Verify Debug] No enrolled students found in database.")
            return jsonify({"status": "fail", "message": "No students enrolled."}), 200

        min_distance = float('inf')
        best_student_id = None

        print(f"[Verify Debug] Starting comparison with {len(encodings)} students. Threshold: {VERIFICATION_THRESHOLD}")

        for student_id, stored_encoding in encodings.items():
            distance = cosine(new_encoding, stored_encoding)
            # Print the score for each comparison!
            print(f"[Verify Debug] Comparing with Student ID {student_id}: Distance = {distance:.4f}")
            
            if distance < min_distance:
                min_distance = distance
                best_student_id = student_id
        
        print(f"[Verify Debug] Best match: Student ID {best_student_id} with distance {min_distance:.4f}")

        if min_distance < VERIFICATION_THRESHOLD:
            print("[Verify Debug] Match FOUND!")
            return jsonify({ "status": "success", "student_id": int(best_student_id) })
        else:
            print("[Verify Debug] Match FAILED. Best score was above threshold.")
            return jsonify({"status": "fail", "message": "Face not recognized."}), 200

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

# --- /delete_enrollment Endpoint (Unchanged) ---
@app.route('/delete_enrollment', methods=['POST'])
def delete_enrollment():
    try:
        data = request.get_json()
        student_id = str(data.get('student_id'))
        
        encodings = load_encodings()
        if student_id in encodings:
            del encodings[student_id]
            save_encodings(encodings)
            return jsonify({"status": "success"})
        else:
            return jsonify({"status": "error", "message": "Student not found."})

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

if __name__ == '__main__':
    # Important: Make sure Flask is running on port 5000 to match your Laravel .env config
    app.run(host='0.0.0.0', port=5000, debug=True)