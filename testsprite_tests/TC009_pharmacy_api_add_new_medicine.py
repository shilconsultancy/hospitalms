import requests
from datetime import datetime, timedelta

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_pharmacy_api_add_new_medicine():
    headers = {
        'Content-Type': 'application/x-www-form-urlencoded'
    }
    # Prepare medicine data
    medicine_data = {
        'name': 'TestMedicineXYZ',
        'quantity': 100,
        'expiry_date': (datetime.now() + timedelta(days=365)).strftime('%Y-%m-%d')
    }

    medicine_id = None

    try:
        # Step 1: Add new medicine (POST)
        response = requests.post(f"{BASE_URL}/pharmacy/medicines", data=medicine_data, headers=headers, timeout=TIMEOUT)
        assert response.status_code == 201 or response.status_code == 200, f"Expected status 200 or 201, got {response.status_code}"
        resp_json = response.json()
        assert 'id' in resp_json, "Response JSON does not contain medicine 'id'"
        medicine_id = resp_json['id']
        assert resp_json['name'] == medicine_data['name'], "Medicine name mismatch in response"
        assert resp_json['quantity'] == medicine_data['quantity'], "Medicine quantity mismatch in response"
        assert resp_json['expiry_date'] == medicine_data['expiry_date'], "Medicine expiry_date mismatch in response"

        # Step 2: Verify inventory update by fetching the medicine list
        get_response = requests.get(f"{BASE_URL}/pharmacy/medicines", timeout=TIMEOUT)
        assert get_response.status_code == 200, f"Failed to get medicines list, status code {get_response.status_code}"
        medicines = get_response.json()
        # Ensure the added medicine is in the list
        filtered = [m for m in medicines if m.get('id') == medicine_id]
        assert len(filtered) == 1, "Added medicine not found in the inventory list"
        found_medicine = filtered[0]
        assert found_medicine['name'] == medicine_data['name'], "Inventory name mismatch"
        assert found_medicine['quantity'] == medicine_data['quantity'], "Inventory quantity mismatch"
        assert found_medicine['expiry_date'] == medicine_data['expiry_date'], "Inventory expiry_date mismatch"

        # Step 3: Test validation: try adding medicine with missing fields or invalid data
        invalid_payloads = [
            {},  # empty
            {'name': '', 'quantity': 10, 'expiry_date': '2030-01-01'},  # empty name
            {'name': 'InvalidMed', 'quantity': -5, 'expiry_date': '2030-01-01'},  # negative quantity
            {'name': 'InvalidMed', 'quantity': 10, 'expiry_date': 'invalid-date'},  # invalid date format
            {'name': 'InvalidMed', 'quantity': 10}  # missing expiry_date
        ]
        for payload in invalid_payloads:
            err_resp = requests.post(f"{BASE_URL}/pharmacy/medicines", data=payload, headers=headers, timeout=TIMEOUT)
            assert err_resp.status_code == 400 or err_resp.status_code == 422, f"Expected client error for payload {payload}, got {err_resp.status_code}"

    finally:
        # Clean up: delete the newly added medicine if applicable
        if medicine_id:
            try:
                del_resp = requests.delete(f"{BASE_URL}/pharmacy/medicines/{medicine_id}", timeout=TIMEOUT)
                # Accept 200, 204, or 404 if already deleted
                assert del_resp.status_code in [200, 204, 404], f"Failed to delete medicine, status {del_resp.status_code}"
            except Exception:
                pass

test_pharmacy_api_add_new_medicine()