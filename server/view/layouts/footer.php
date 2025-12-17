                </div> <!-- Close container-fluid -->
            </div> <!-- Close main-content -->
        </div> <!-- Close row -->
    </div> <!-- Close container-fluid -->

    <!-- Footer -->
    <footer class="py-3" style="color: #292d33;">
        <div class="container-fluid" style="color: #292d33;">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 - TỎA SÁNG Resort Management System - Copyright TỎA SÁNG </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">User: <?php echo $user['username']; ?> | Role: <?php echo $role; ?></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Active menu highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>