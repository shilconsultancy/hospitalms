import requests
import datetime

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
HEADERS = {
    "Content-Type": "application/x-www-form-urlencoded"
}

def create_patient():
    url = f"{BASE_URL}/patients"
    patient_data = {
        "name": "Test Patient Billing",
        "age": 30,
        "gender": "Other"
    }
    resp = requests.post(url, data=patient_data, headers=HEADERS, timeout=TIMEOUT)
    resp.raise_for_status()
    patient = resp.json()
    assert "id" in patient and isinstance(patient["id"], int)
    return patient["id"]

def delete_patient(patient_id):
    url = f"{BASE_URL}/patients/{patient_id}"
    # Assuming DELETE endpoint exists for cleanup, otherwise skip
    try:
        resp = requests.delete(url, timeout=TIMEOUT)
        # If delete is not implemented, it's ok to ignore failure
    except Exception:
        pass

def delete_bill(bill_id):
    url = f"{BASE_URL}/bills/{bill_id}"
    # Assuming DELETE endpoint exists for cleanup, otherwise skip
    try:
        resp = requests.delete(url, timeout=TIMEOUT)
    except Exception:
        pass

def test_billing_api_create_new_bill():
    patient_id = None
    bill_id = None
    try:
        # Step 1: Create a patient to bill against
        patient_id = create_patient()

        # Step 2: Post a new bill for this patient
        url = f"{BASE_URL}/bills"
        bill_data = {
            "patient_id": patient_id,
            "amount": 235.50,
            "description": "Consultation and lab tests"
        }
        response = requests.post(url, data=bill_data, headers=HEADERS, timeout=TIMEOUT)
        assert response.status_code == 201, f"Expected status 201, got {response.status_code}"
        bill = response.json()
        
        # Validate the bill response has meaningful data and matches request
        assert "id" in bill and isinstance(bill["id"], int)
        bill_id = bill["id"]
        assert bill["patient_id"] == patient_id
        assert abs(float(bill["amount"]) - 235.50) < 0.01
        assert bill["description"] == "Consultation and lab tests"

        # Step 3: Retrieve bill via GET /bills to verify storage
        bills_get_resp = requests.get(f"{BASE_URL}/bills", timeout=TIMEOUT)
        assert bills_get_resp.status_code == 200
        bills_list = bills_get_resp.json()
        assert any(b["id"] == bill_id for b in bills_list), "Created bill not found in bills list"
        
    finally:
        if bill_id is not None:
            delete_bill(bill_id)
        if patient_id is not None:
            delete_patient(patient_id)

test_billing_api_create_new_bill()