import requests
from datetime import datetime, timedelta

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
HEADERS = {"Content-Type": "application/x-www-form-urlencoded"}


def create_patient(name, age, gender):
    data = {"name": name, "age": age, "gender": gender}
    response = requests.post(f"{BASE_URL}/patients", data=data, headers=HEADERS, timeout=TIMEOUT)
    response.raise_for_status()
    return response.json()["id"]


def delete_patient(patient_id):
    requests.delete(f"{BASE_URL}/patients/{patient_id}", timeout=TIMEOUT)  # Assume DELETE available


def create_staff(name, role, department):
    data = {"name": name, "role": role, "department": department}
    response = requests.post(f"{BASE_URL}/staff", data=data, headers=HEADERS, timeout=TIMEOUT)
    response.raise_for_status()
    return response.json()["id"]


def delete_staff(staff_id):
    requests.delete(f"{BASE_URL}/staff/{staff_id}", timeout=TIMEOUT)  # Assume DELETE available


def delete_surgery(surgery_id):
    requests.delete(f"{BASE_URL}/surgeries/{surgery_id}", timeout=TIMEOUT)  # Assume DELETE available


def test_schedule_new_surgery():
    patient_id = None
    surgeon_id = None
    surgery_id = None
    try:
        # Create a patient to schedule surgery for
        patient_id = create_patient("Test Patient Surgery", 30, "Other")

        # Create a staff member with role 'surgeon' (assuming role names are recognized)
        surgeon_id = create_staff("Dr. Surgery", "surgeon", "Surgery Department")

        # Prepare surgery date (tomorrow)
        surgery_date = (datetime.now() + timedelta(days=1)).date().isoformat()

        # Schedule new surgery
        surgery_data = {
            "patient_id": patient_id,
            "surgeon_id": surgeon_id,
            "surgery_date": surgery_date
        }
        response = requests.post(f"{BASE_URL}/surgeries", data=surgery_data, headers=HEADERS, timeout=TIMEOUT)

        # Validate response status and content
        assert response.status_code == 201 or response.status_code == 200, f"Unexpected status code: {response.status_code}"
        res_json = response.json()
        assert "id" in res_json, "Response missing surgery ID"
        surgery_id = res_json["id"]

        # Validate that the scheduled surgery data matches input
        assert res_json.get("patient_id") == patient_id, "Patient ID mismatch"
        assert res_json.get("surgeon_id") == surgeon_id, "Surgeon ID mismatch"
        assert res_json.get("surgery_date") == surgery_date, "Surgery date mismatch"

        # Optionally, verify the surgery is retrievable and correct
        get_resp = requests.get(f"{BASE_URL}/surgeries/{surgery_id}", timeout=TIMEOUT)
        assert get_resp.status_code == 200, f"Failed to retrieve scheduled surgery, status: {get_resp.status_code}"
        surgery_info = get_resp.json()
        assert surgery_info.get("id") == surgery_id, "Retrieved surgery ID mismatch"
        assert surgery_info.get("patient_id") == patient_id, "Retrieved patient ID mismatch"
        assert surgery_info.get("surgeon_id") == surgeon_id, "Retrieved surgeon ID mismatch"
        assert surgery_info.get("surgery_date") == surgery_date, "Retrieved surgery date mismatch"

    except requests.RequestException as e:
        assert False, f"Request failed: {e}"
    finally:
        # Cleanup created resources to maintain test isolation
        if surgery_id:
            try:
                delete_surgery(surgery_id)
            except Exception:
                pass
        if patient_id:
            try:
                delete_patient(patient_id)
            except Exception:
                pass
        if surgeon_id:
            try:
                delete_staff(surgeon_id)
            except Exception:
                pass


test_schedule_new_surgery()