import requests

BASE_URL = "http://localhost:8000"
LOGIN_ENDPOINT = f"{BASE_URL}/login"
TIMEOUT = 30

def test_authentication_api_user_login():
    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }

    # Valid credentials test (assuming test user exists: username=testuser, password=testpass)
    valid_payload = {
        "username": "testuser",
        "password": "testpass"
    }
    try:
        response = requests.post(LOGIN_ENDPOINT, data=valid_payload, headers=headers, timeout=TIMEOUT)
        assert response.status_code == 200, f"Expected 200 for valid login, got {response.status_code}"
        # Assuming response contains JSON with auth token/session info
        json_response = response.json()
        assert "token" in json_response or "session_id" in json_response, "Authentication token/session not found in response"
    except requests.RequestException as e:
        assert False, f"RequestException on valid login: {e}"

    # Invalid credentials test
    invalid_payload = {
        "username": "invaliduser",
        "password": "wrongpassword"
    }
    try:
        response = requests.post(LOGIN_ENDPOINT, data=invalid_payload, headers=headers, timeout=TIMEOUT)
        # Expecting unauthorized or bad request, typically 401 or 400
        assert response.status_code in (400, 401), f"Expected 400 or 401 for invalid login, got {response.status_code}"
        # Response may contain an error message
        json_response = response.json()
        assert "error" in json_response or "message" in json_response, "Error message not found in invalid login response"
    except requests.RequestException as e:
        assert False, f"RequestException on invalid login: {e}"

test_authentication_api_user_login()