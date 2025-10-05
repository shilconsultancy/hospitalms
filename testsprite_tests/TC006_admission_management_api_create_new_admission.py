import requests
import datetime

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_admission_management_api_create_new_admission():
    # Helper function to create patient
    def create_patient():
        patient_data = {
            'name': 'Test Patient',
            'age': 30,
            'gender': 'Male'
        }
        resp = requests.post(f"{BASE_URL}/patients", data=patient_data, timeout=TIMEOUT)
        resp.raise_for_status()
        patient = resp.json()
        assert 'id' in patient and isinstance(patient['id'], int)
        return patient['id']

    # Helper function to create ward
    def create_ward():
        ward_data = {
            'name': 'Test Ward',
            'capacity': 10,
            'department': 'General'
        }
        resp = requests.post(f"{BASE_URL}/wards", data=ward_data, timeout=TIMEOUT)
        resp.raise_for_status()
        ward = resp.json()
        assert 'id' in ward and isinstance(ward['id'], int)
        return ward['id']

    # Helper function to create bed
    def create_bed(ward_id):
        bed_data = {
            'ward_id': ward_id,
            'bed_number': 'B101',
            'bed_type': 'General'
        }
        resp = requests.post(f"{BASE_URL}/beds", data=bed_data, timeout=TIMEOUT)
        resp.raise_for_status()
        bed = resp.json()
        assert 'id' in bed and isinstance(bed['id'], int)
        return bed['id']

    # Helper function to delete admission
    def delete_admission(admission_id):
        try:
            resp = requests.delete(f"{BASE_URL}/admissions/{admission_id}", timeout=TIMEOUT)
            if resp.status_code not in (200, 204, 404):
                resp.raise_for_status()
        except Exception:
            pass

    # Helper function to delete patient
    def delete_patient(patient_id):
        try:
            resp = requests.delete(f"{BASE_URL}/patients/{patient_id}", timeout=TIMEOUT)
            if resp.status_code not in (200, 204, 404):
                resp.raise_for_status()
        except Exception:
            pass

    # Helper function to delete bed
    def delete_bed(bed_id):
        try:
            resp = requests.delete(f"{BASE_URL}/beds/{bed_id}", timeout=TIMEOUT)
            if resp.status_code not in (200, 204, 404):
                resp.raise_for_status()
        except Exception:
            pass

    # Helper function to delete ward
    def delete_ward(ward_id):
        try:
            resp = requests.delete(f"{BASE_URL}/wards/{ward_id}", timeout=TIMEOUT)
            if resp.status_code not in (200, 204, 404):
                resp.raise_for_status()
        except Exception:
            pass

    admission_id = None
    patient_id = None
    ward_id = None
    bed_id = None

    try:
        # Create prerequisite resources
        patient_id = create_patient()
        ward_id = create_ward()
        bed_id = create_bed(ward_id)

        # Test creating admission with valid data
        admission_data = {
            'patient_id': patient_id,
            'ward_id': ward_id,
            'bed_id': bed_id
        }
        resp = requests.post(f"{BASE_URL}/admissions", data=admission_data, timeout=TIMEOUT)
        assert resp.status_code == 201 or resp.status_code == 200
        admission = resp.json()
        assert 'id' in admission and isinstance(admission['id'], int)
        admission_id = admission['id']
        assert admission['patient_id'] == patient_id
        assert admission['ward_id'] == ward_id
        assert admission['bed_id'] == bed_id

        # Test data validation: missing patient_id
        invalid_data_missing_patient = {
            'ward_id': ward_id,
            'bed_id': bed_id
        }
        resp = requests.post(f"{BASE_URL}/admissions", data=invalid_data_missing_patient, timeout=TIMEOUT)
        assert resp.status_code >= 400 and resp.status_code < 500

        # Test data validation: invalid ward_id (string)
        invalid_data_invalid_ward = {
            'patient_id': patient_id,
            'ward_id': 'invalid',
            'bed_id': bed_id
        }
        resp = requests.post(f"{BASE_URL}/admissions", data=invalid_data_invalid_ward, timeout=TIMEOUT)
        assert resp.status_code >= 400 and resp.status_code < 500

        # Test data validation: invalid bed_id (negative integer)
        invalid_data_invalid_bed = {
            'patient_id': patient_id,
            'ward_id': ward_id,
            'bed_id': -1
        }
        resp = requests.post(f"{BASE_URL}/admissions", data=invalid_data_invalid_bed, timeout=TIMEOUT)
        assert resp.status_code >= 400 and resp.status_code < 500

    finally:
        # Cleanup created admission, patient, bed, ward
        if admission_id is not None:
            delete_admission(admission_id)
        if patient_id is not None:
            delete_patient(patient_id)
        if bed_id is not None:
            delete_bed(bed_id)
        if ward_id is not None:
            delete_ward(ward_id)

test_admission_management_api_create_new_admission()