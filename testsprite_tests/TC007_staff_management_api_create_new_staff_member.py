import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_create_new_staff_member():
    url = f"{BASE_URL}/staff"
    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }
    # Valid test data for creating a staff member
    payload = {
        "name": "John Doe",
        "role": "Nurse",
        "department": "Emergency"
    }

    try:
        response = requests.post(url, data=payload, headers=headers, timeout=TIMEOUT)
        # Assert response code 201 Created or 200 OK depending on API implementation
        assert response.status_code in (200, 201), f"Unexpected status code: {response.status_code}"
        # Validate response content type and JSON body
        content_type = response.headers.get("Content-Type", "")
        assert "application/json" in content_type, f"Unexpected content type: {content_type}"

        data = response.json()
        # Validate that returned data contains expected fields and matches submitted data
        assert "id" in data, "Response JSON missing 'id' field"
        assert data["name"] == payload["name"], "Name mismatch in response"
        assert data["role"] == payload["role"], "Role mismatch in response"
        assert data["department"] == payload["department"], "Department mismatch in response"

    except requests.exceptions.RequestException as e:
        assert False, f"Request failed: {e}"

test_create_new_staff_member()