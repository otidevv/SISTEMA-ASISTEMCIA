<!-- Footer -->
<footer>
    <div class="footer-content">
        <div class="footer-grid">
            <!-- Logo & Info -->
            <div class="footer-column" style="padding: 20px;">
                <img src="{{ asset('assets_cepre/img/logo/logocepre1.svg') }}" onerror="this.onerror=null; this.src='https://placehold.co/150x60/2C5F7C/ffffff?text=LOGO';" alt="CEPRE UNAMAD" class="footer-logo" style="filter: brightness(0) invert(1);">
                <p style="font-size: 14px; line-height: 1.6; opacity: 0.8; margin-bottom: 25px;">
                    Centro Pre Universitario de la UNAMAD<br>
                    Av. Dos de Mayo N° 960<br>
                    Puerto Maldonado - Tambopata<br>
                    Madre de Dios - Perú
                </p>
                <div class="social-links-container">
                    <p style="font-weight: 700; margin-bottom: 15px; color: var(--verde-cepre); font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Siguenos</p>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>

            <!-- Admissions -->
            <div class="footer-column">
                <h3>Admisiones</h3>
                <ul>
                    <li><a href="{{ route('public.vacantes') }}">Vacantes</a></li>
                    <li><a href="{{ route('public.cursos') }}">Cursos Académicos</a></li>
                    <li><a href="{{ route('home') }}#contacto">Cómo Postular</a></li>
                    <li><a href="#">Cronograma</a></li>
                    <li><a href="#">Requisitos</a></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="{{ route('public.carreras') }}">Carreras Profesionales</a></li>
                    <li><a href="{{ route('resultados-examenes.public') }}">Resultados de Exámenes</a></li>
                    <li><a href="{{ route('login') }}">Portal de Estudiantes</a></li>
                    <li><a href="{{ route('login') }}">Portal de Docentes</a></li>
                    <li><a href="#">Boletines</a></li>
                </ul>
            </div>

            <!-- Additional Links & Botones -->
            <div class="footer-column">
                <h3>Enlaces Adicionales</h3>
                <ul>
                    <li><a href="#">Casa Abierta</a></li>
                    <li><a href="#">Escuela de Verano</a></li>
                    <li><a href="#">Eventos 2024</a></li>
                    <li><a href="#">Foro Académico</a></li>
                    <li><a href="#">Términos y Condiciones</a></li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="copyright">
            <p>Copyright © 2026 CEPRE UNAMAD. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
