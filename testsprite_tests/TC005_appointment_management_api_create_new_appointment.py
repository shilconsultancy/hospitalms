import requests
from datetime import date, timedelta

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def create_patient(name, age, gender):
    payload = {
        'name': name,
        'age': age,
        'gender': gender
    }
    response = requests.post(f"{BASE_URL}/patients", data=payload, timeout=TIMEOUT)
    response.raise_for_status()
    return response.json().get('id')

def delete_patient(patient_id):
    # Assuming there's an endpoint to delete a patient: DELETE /patients/{id}
    requests.delete(f"{BASE_URL}/patients/{patient_id}", timeout=TIMEOUT)

def create_doctor(name, role="doctor", department="General"):
    payload = {
        'name': name,
        'role': role,
        'department': department
    }
    response = requests.post(f"{BASE_URL}/staff", data=payload, timeout=TIMEOUT)
    response.raise_for_status()
    return response.json().get('id')

def delete_doctor(doctor_id):
    # Assuming there's an endpoint to delete staff: DELETE /staff/{id}
    requests.delete(f"{BASE_URL}/staff/{doctor_id}", timeout=TIMEOUT)

def test_create_new_appointment():
    patient_id = None
    doctor_id = None
    appointment_id = None
    try:
        # Create patient
        patient_id = create_patient("Test Patient", 30, "Male")
        assert patient_id is not None and isinstance(patient_id, int), "Failed to create patient"

        # Create doctor (staff with role doctor)
        doctor_id = create_doctor("Test Doctor", role="doctor", department="General")
        assert doctor_id is not None and isinstance(doctor_id, int), "Failed to create doctor"

        # Prepare appointment date (tomorrow)
        appointment_date = (date.today() + timedelta(days=1)).isoformat()

        appointment_payload = {
            'patient_id': patient_id,
            'doctor_id': doctor_id,
            'appointment_date': appointment_date
        }

        # Create appointment
        response = requests.post(f"{BASE_URL}/appointments", data=appointment_payload, timeout=TIMEOUT)
        assert response.status_code == 201 or response.status_code == 200, f"Unexpected status code: {response.status_code}"
        
        appointment_data = response.json()
        assert appointment_data.get('id') is not None, "Appointment ID missing in response"
        assert appointment_data.get('patient_id') == patient_id, "Patient ID mismatch"
        assert appointment_data.get('doctor_id') == doctor_id, "Doctor ID mismatch"
        assert appointment_data.get('appointment_date') == appointment_date, "Appointment date mismatch"
        
        appointment_id = appointment_data.get('id')

        # Retrieve appointment list to verify storage
        list_response = requests.get(f"{BASE_URL}/appointments", timeout=TIMEOUT)
        assert list_response.status_code == 200, f"Failed to get appointments list: {list_response.status_code}"
        appointments = list_response.json()
        assert any(appt.get('id') == appointment_id for appt in appointments), "Created appointment not found in list"

    finally:
        # Cleanup appointment if deletion endpoint exists
        if appointment_id is not None:
            requests.delete(f"{BASE_URL}/appointments/{appointment_id}", timeout=TIMEOUT)
        # Cleanup doctor
        if doctor_id is not None:
            delete_doctor(doctor_id)
        # Cleanup patient
        if patient_id is not None:
            delete_patient(patient_id)

test_create_new_appointment()