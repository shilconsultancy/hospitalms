import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_authentication_api_user_logout():
    session = requests.Session()
    try:
        # First, login to get a valid session or token for logout
        login_url = f"{BASE_URL}/login"
        login_data = {
            "username": "testuser",
            "password": "testpassword"
        }
        headers = {"Content-Type": "application/x-www-form-urlencoded"}
        login_response = session.post(login_url, data=login_data, headers=headers, timeout=TIMEOUT)
        assert login_response.status_code == 200, f"Login failed with status code {login_response.status_code}"
        # Assuming successful login returns a session cookie or token in headers/cookies

        # Now call logout endpoint
        logout_url = f"{BASE_URL}/logout"
        logout_response = session.post(logout_url, timeout=TIMEOUT)
        assert logout_response.status_code == 200 or logout_response.status_code == 204, \
            f"Logout failed with status code {logout_response.status_code}"

        # After logout, verify session termination by accessing a protected resource
        dashboard_url = f"{BASE_URL}/dashboard"
        dashboard_response = session.get(dashboard_url, timeout=TIMEOUT)
        # Expect unauthorized or forbidden after logout (commonly 401 or 403)
        assert dashboard_response.status_code in {401, 403}, \
            f"Access after logout not revoked, status code: {dashboard_response.status_code}"

    except requests.RequestException as e:
        assert False, f"RequestException occurred: {e}"

test_authentication_api_user_logout()
