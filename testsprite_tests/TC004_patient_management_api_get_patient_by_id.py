import requests
import json

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_patient_management_api_get_patient_by_id():
    patient_data = {
        "name": "Test Patient TC004",
        "age": 30,
        "gender": "Male"
    }

    created_patient_id = None

    try:
        # Create a new patient to get a valid patient_id
        create_response = requests.post(
            f"{BASE_URL}/patients",
            data=patient_data,
            timeout=TIMEOUT
        )
        assert create_response.status_code == 201, f"Patient creation failed: {create_response.text}"
        created_patient = create_response.json()
        assert "id" in created_patient, "Created patient response missing id"
        created_patient_id = created_patient["id"]

        # Test GET /patients/{id} with valid ID
        get_response = requests.get(
            f"{BASE_URL}/patients/{created_patient_id}",
            timeout=TIMEOUT
        )
        assert get_response.status_code == 200, f"Failed to get patient by valid ID: {get_response.text}"
        patient = get_response.json()

        # Validate the returned patient data matches what was created
        assert patient["id"] == created_patient_id, "Patient ID mismatch"
        assert patient["name"] == patient_data["name"], "Patient name mismatch"
        assert patient["age"] == patient_data["age"], "Patient age mismatch"
        assert patient["gender"] == patient_data["gender"], "Patient gender mismatch"

        # Test GET /patients/{id} with invalid ID (e.g., non-existent)
        invalid_id = 999999999
        invalid_response = requests.get(
            f"{BASE_URL}/patients/{invalid_id}",
            timeout=TIMEOUT
        )
        assert invalid_response.status_code in (400, 404), f"Expected 400 or 404 for invalid ID, got {invalid_response.status_code}"
        # Optional: check error message content if JSON returned
        try:
            error_json = invalid_response.json()
            assert "error" in error_json or "message" in error_json, "Error response should contain error or message"
        except json.JSONDecodeError:
            # If not JSON, pass
            pass

        # Test GET /patients/{id} with invalid format ID (e.g., string)
        invalid_format_id = "abc"
        invalid_format_response = requests.get(
            f"{BASE_URL}/patients/{invalid_format_id}",
            timeout=TIMEOUT
        )
        assert invalid_format_response.status_code in (400, 404), f"Expected 400 or 404 for invalid format ID, got {invalid_format_response.status_code}"
        try:
            error_json = invalid_format_response.json()
            assert "error" in error_json or "message" in error_json, "Error response should contain error or message"
        except json.JSONDecodeError:
            pass

    finally:
        if created_patient_id is not None:
            # Delete the created patient after test to clean up
            try:
                delete_response = requests.delete(
                    f"{BASE_URL}/patients/{created_patient_id}",
                    timeout=TIMEOUT
                )
                # Accept 200 OK or 204 No Content on successful delete
                assert delete_response.status_code in (200, 204), f"Failed to delete patient cleanup. Status code: {delete_response.status_code}"
            except Exception:
                pass


test_patient_management_api_get_patient_by_id()