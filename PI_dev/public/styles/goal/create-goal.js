// Multi-Step Form Logic for Create Goal

let currentStep = 1;
const totalSteps = 3;

function updateSteps() {
    // Update step circles
    document.querySelectorAll('.step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        if (stepNum < currentStep) {
            step.classList.add('completed');
            step.classList.remove('active');
        } else if (stepNum === currentStep) {
            step.classList.add('active');
            step.classList.remove('completed');
        } else {
            step.classList.remove('active', 'completed');
        }
    });

    // Update form steps
    document.querySelectorAll('.form-step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        if (stepNum === currentStep) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });

    // Update confirmation on step 3
    if (currentStep === 3) {
        updateConfirmation();
    }
}

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateSteps();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateSteps();
    }
}

function validateCurrentStep() {
    const currentFormStep = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const inputs = currentFormStep.querySelectorAll('input[required], textarea[required], select[required]');
    
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'red';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields');
    }

    return isValid;
}

function updateConfirmation() {
    const title = document.querySelector('[name="goal[title]"]').value || '-';
    const description = document.querySelector('[name="goal[description]"]').value || '-';
    const startDate = document.querySelector('[name="goal[startDate]"]').value || '-';
    const endDate = document.querySelector('[name="goal[endDate]"]').value || '-';
    const status = document.querySelector('[name="goal[status]"]').value || '-';

    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-description').textContent = description;
    document.getElementById('confirm-start').textContent = startDate;
    document.getElementById('confirm-end').textContent = endDate;
    document.getElementById('confirm-status').textContent = status;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSteps();
});
