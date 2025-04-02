// Confirm delete
function confirmDelete(type, id) {
  if (confirm(`Are you sure you want to delete this ${type}?`)) {
    return true
  }
  return false
}

// Initialize tooltips
document.addEventListener("DOMContentLoaded", () => {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
})

// Form validation
document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll(".needs-validation")

  Array.from(forms).forEach((form) => {
    form.addEventListener(
      "submit",
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add("was-validated")
      },
      false,
    )
  })
})

