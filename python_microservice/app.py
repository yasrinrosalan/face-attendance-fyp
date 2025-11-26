import os
import base64
import re
import json
import cv2 # OpenCV for image analysis
import numpy as np # Number crunching
from flask import Flask, request, jsonify
from deepface import DeepFace
from scipy.spatial.distance import cosine

app = Flask(__name__)

# --- Configuration ---
MODEL_NAME = "VGG-Face"
VERIFY_TEMP_FILE = "temp_verify.jpg"
ENCODINGS_FILE = "student_encodings.json" 
VERIFICATION_THRESHOLD = 0.4 

# --- Helper Function (Unchanged) ---
def decode_base64_image(data_url, output_path):
    try:
        img_str = re.search(r'base64,(.*)', data_url).group(1)
        img_data = base64.b64decode(img_str)
        with open(output_path, 'wb') as f:
            f.write(img_data)
        return True
    except Exception as e:
        print(f"Error decoding base64 image: {e}")
        return False

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

# --- /enroll Endpoint (MODIFIED: REMOVED CUSTOM QA) ---
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
            # 2. Generate embedding (DeepFace handles QA internally)
            # enforce_detection=True ensures a face is found and is of good enough quality
            embedding_obj = DeepFace.represent(
                img_path=VERIFY_TEMP_FILE,
                model_name=MODEL_NAME,
                enforce_detection=True
            )
            encoding = embedding_obj[0]["embedding"]
        
        except ValueError as ve:
            # This catches cases where DeepFace cannot find a face or the image is too poor
            return jsonify({"status": "error", "message": "No face detected or image quality too low. Please try again."}), 400
        except Exception as e:
             return jsonify({"status": "error", "message": str(e)}), 500
        finally:
            if os.path.exists(VERIFY_TEMP_FILE):
                os.remove(VERIFY_TEMP_FILE)

        # 3. Save to JSON
        encodings = load_encodings()
        encodings[student_id] = encoding
        save_encodings(encodings)

        print(f"Successfully enrolled student {student_id}")
        return jsonify({ "status": "success", "message": "Face enrolled successfully!" })

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


# --- /verify Endpoint (Unchanged) ---
@app.route('/verify', methods=['POST'])
def verify():
    try:
        data = request.get_json()
        image_base64 = data.get('image_base64')

        if not image_base64:
            return jsonify({"status": "error", "message": "Missing image_base64"}), 400

        if not decode_base64_image(image_base64, VERIFY_TEMP_FILE):
            return jsonify({"status": "error", "message": "Failed to decode image"}), 500

        try:
            embedding_obj = DeepFace.represent(
                img_path=VERIFY_TEMP_FILE,
                model_name=MODEL_NAME,
                enforce_detection=True
            )
            new_encoding = embedding_obj[0]["embedding"]
        except ValueError as ve:
            return jsonify({"status": "fail", "message": "No face detected."})
        finally:
            if os.path.exists(VERIFY_TEMP_FILE):
                os.remove(VERIFY_TEMP_FILE)

        encodings = load_encodings()
        if not encodings:
            return jsonify({"status": "fail", "message": "No students enrolled."})

        min_distance = float('inf')
        best_student_id = None

        for student_id, stored_encoding in encodings.items():
            distance = cosine(new_encoding, stored_encoding)
            if distance < min_distance:
                min_distance = distance
                best_student_id = student_id
        
        if min_distance < VERIFICATION_THRESHOLD:
            return jsonify({ "status": "success", "student_id": int(best_student_id) })
        else:
            return jsonify({"status": "fail", "message": "Face not recognized."})

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
    app.run(host='0.0.0.0', port=5000, debug=True)