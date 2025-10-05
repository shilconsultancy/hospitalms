import requests
import uuid

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_patient_management_api_create_new_patient():
    # Removed Content-Type header to allow requests to send as form data
    
    # Valid patient data
    valid_patient_data = {
        "name": f"Test Patient {uuid.uuid4()}",
        "age": 30,
        "gender": "Male"
    }

    # Invalid patient data samples
    invalid_patient_data_cases = [
        # Missing name
        {
            "age": 30,
            "gender": "Male"
        },
        # Missing age
        {
            "name": "Missing Age Patient",
            "gender": "Female"
        },
        # Missing gender
        {
            "name": "Missing Gender Patient",
            "age": 25
        },
        # Age as string
        {
            "name": "Invalid Age Patient",
            "age": "twenty",
            "gender": "Male"
        },
        # Gender as number
        {
            "name": "Invalid Gender Patient",
            "age": 40,
            "gender": 123
        },
        # Empty name
        {
            "name": "",
            "age": 22,
            "gender": "Female"
        }
    ]

    created_patient_id = None

    try:
        # Test valid patient creation
        response = requests.post(
            f"{BASE_URL}/patients",
            data=valid_patient_data,
            timeout=TIMEOUT
        )
        assert response.status_code in [200, 201], f"Expected 201 or 200, got {response.status_code}"
        resp_json = response.json()
        assert "id" in resp_json, "Response JSON missing 'id' for created patient"
        created_patient_id = resp_json["id"]
        assert resp_json.get("name") == valid_patient_data["name"], "Created patient name mismatch"
        assert int(resp_json.get("age")) == valid_patient_data["age"], "Created patient age mismatch"
        assert resp_json.get("gender") == valid_patient_data["gender"], "Created patient gender mismatch"

        # Verify patient persistence via GET /patients/{id}
        get_resp = requests.get(f"{BASE_URL}/patients/{created_patient_id}", timeout=TIMEOUT)
        assert get_resp.status_code == 200, f"Expected 200 from GET patient by id, got {get_resp.status_code}"
        get_data = get_resp.json()
        assert get_data.get("name") == valid_patient_data["name"], "Persisted patient name mismatch"
        assert int(get_data.get("age")) == valid_patient_data["age"], "Persisted patient age mismatch"
        assert get_data.get("gender") == valid_patient_data["gender"], "Persisted patient gender mismatch"

        # Test invalid patient data
        for invalid_data in invalid_patient_data_cases:
            invalid_response = requests.post(
                f"{BASE_URL}/patients",
                data=invalid_data,
                timeout=TIMEOUT
            )
            assert 400 <= invalid_response.status_code < 500, \
                f"Expected 4xx for invalid input, got {invalid_response.status_code}, data: {invalid_data}"

    finally:
        # Clean up created patient if exists
        if created_patient_id:
            try:
                del_resp = requests.delete(f"{BASE_URL}/patients/{created_patient_id}", timeout=TIMEOUT)
                # Accept 200 OK or 204 No Content or 404 Not Found as success for delete
                assert del_resp.status_code in [200, 204, 404], f"Unexpected status code on patient delete: {del_resp.status_code}"
            except Exception:
                pass


test_patient_management_api_create_new_patient()
