</div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Event Management System</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm delete
        function confirmDelete(type, id) {
            if (confirm(`Are you sure you want to delete this ${type}?`)) {
                return true;
            }
            return false;
        }

        // Initialize tooltips
        document.addEventListener("DOMContentLoaded", () => {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Form validation
        document.addEventListener("DOMContentLoaded", () => {
            const forms = document.querySelectorAll(".needs-validation");

            Array.from(forms).forEach((form) => {
                form.addEventListener(
                    "submit",
                    (event) => {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        form.classList.add("was-validated");
                    },
                    false,
                );
            });
        });

        function shareEvent(platform) {
            const eventUrl = window.location.href;
            const eventTitle = document.querySelector('h2').innerText;
            
            if (platform === 'facebook') {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(eventUrl)}`, '_blank');
            } else if (platform === 'twitter') {
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent('Check out this event: ' + eventTitle)}&url=${encodeURIComponent(eventUrl)}`, '_blank');
            }
        }

        function copyEventLink() {
            const eventUrl = window.location.href;
            navigator.clipboard.writeText(eventUrl).then(() => {
                alert('Event link copied to clipboard!');
            });
        }
    </script>
</body>
</html>

