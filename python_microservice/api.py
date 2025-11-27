import os
import base64
import re
import json
import cv2
import numpy as np
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import Union # <--- Added this
from deepface import DeepFace
from scipy.spatial.distance import cosine

# Initialize FastAPI
app = FastAPI(title="Face Recognition System", version="1.0")

# --- Configuration ---
MODEL_NAME = "Facenet512"
DETECTOR_BACKEND = "mtcnn"
VERIFY_TEMP_FILE = "temp_verify.jpg"
ENCODINGS_FILE = "student_encodings.json"
VERIFICATION_THRESHOLD = 0.40
LIVENESS_TEXTURE_THRESHOLD = 30.0
LIVENESS_GLARE_THRESHOLD = 0.01

# --- Pydantic Models (Updated to accept Int or String) ---
class EnrollRequest(BaseModel):
    student_id: Union[int, str] # <--- Allows both formats
    image_base64: str

class VerifyRequest(BaseModel):
    image_base64: str

class DeleteRequest(BaseModel):
    student_id: Union[int, str] # <--- Allows both formats

# --- Helper Functions ---
def decode_base64_image(data_url, output_path):
    try:
        if ',' in data_url:
            img_str = data_url.split(',')[1]
        else:
            img_str = data_url
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

def check_liveness_potential(image_path):
    try:
        img = cv2.imread(image_path)
        if img is None: return False, "Cannot read image"
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        texture_score = cv2.Laplacian(gray, cv2.CV_64F).var()
        print(f"[Liveness] Texture: {texture_score:.2f}")
        if texture_score < LIVENESS_TEXTURE_THRESHOLD:
            return False, "Image too blurry/smooth (Possible Screen)"

        return True, "Passed"
    except Exception as e:
        print(f"Liveness Error: {e}")
        return True, "Check Skipped"

# --- API ENDPOINTS ---

@app.post("/enroll")
def enroll(data: EnrollRequest):
    # Ensure student_id is stored as a string for consistency in JSON
    str_student_id = str(data.student_id)
    
    if not decode_base64_image(data.image_base64, VERIFY_TEMP_FILE):
        return {"status": "error", "message": "Failed to decode image"}

    try:
        # Liveness
        is_live, msg = check_liveness_potential(VERIFY_TEMP_FILE)
        if not is_live:
            return {"status": "error", "message": msg}

        # Embedding
        embedding_obj = DeepFace.represent(
            img_path=VERIFY_TEMP_FILE,
            model_name=MODEL_NAME,
            detector_backend=DETECTOR_BACKEND,
            enforce_detection=True
        )
        encoding = embedding_obj[0]["embedding"]

        # Save
        encodings = load_encodings()
        encodings[str_student_id] = encoding
        save_encodings(encodings)

        print(f"Enrolled student {str_student_id}")
        return {"status": "success", "message": "Face enrolled successfully!"}

    except ValueError:
        return {"status": "error", "message": "No face detected."}
    except Exception as e:
        print(f"Error: {e}")
        return {"status": "error", "message": str(e)}
    finally:
        if os.path.exists(VERIFY_TEMP_FILE):
            os.remove(VERIFY_TEMP_FILE)


@app.post("/verify")
def verify(data: VerifyRequest):
    if not decode_base64_image(data.image_base64, VERIFY_TEMP_FILE):
        return {"status": "error", "message": "Failed to decode image"}

    try:
        is_live, msg = check_liveness_potential(VERIFY_TEMP_FILE)
        if not is_live:
            return {"status": "fail", "message": msg}

        embedding_obj = DeepFace.represent(
            img_path=VERIFY_TEMP_FILE,
            model_name=MODEL_NAME,
            detector_backend=DETECTOR_BACKEND,
            enforce_detection=True
        )
        new_encoding = embedding_obj[0]["embedding"]

        encodings = load_encodings()
        if not encodings:
            return {"status": "fail", "message": "No students enrolled."}

        min_distance = float('inf')
        best_student_id = None

        for student_id, stored_encoding in encodings.items():
            distance = cosine(new_encoding, stored_encoding)
            if distance < min_distance:
                min_distance = distance
                best_student_id = student_id
        
        print(f"Match Distance: {min_distance:.4f} (ID: {best_student_id})")

        if min_distance < VERIFICATION_THRESHOLD:
            return {"status": "success", "student_id": int(best_student_id)}
        else:
            return {"status": "fail", "message": "Face not recognized."}

    except ValueError:
        return {"status": "fail", "message": "No face detected."}
    except Exception as e:
        print(f"Error: {e}")
        return {"status": "error", "message": str(e)}
    finally:
        if os.path.exists(VERIFY_TEMP_FILE):
            os.remove(VERIFY_TEMP_FILE)

@app.post("/delete_enrollment")
def delete_enrollment(data: DeleteRequest):
    try:
        encodings = load_encodings()
        str_id = str(data.student_id)
        if str_id in encodings:
            del encodings[str_id]
            save_encodings(encodings)
            return {"status": "success"}
        else:
            return {"status": "error", "message": "Student not found."}
    except Exception as e:
        return {"status": "error", "message": str(e)}