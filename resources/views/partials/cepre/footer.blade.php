<!-- Footer -->
<footer>
    <div class="footer-content">
        <div class="footer-grid">
            <!-- Logo & Info -->
            <div class="footer-column">
                <img src="{{ asset('assets_cepre/img/logo/logo2_0.svg') }}" onerror="this.onerror=null; this.src='https://placehold.co/150x60/2C5F7C/ffffff?text=LOGO';" alt="CEPRE UNAMAD" class="footer-logo">
                <div class="contact-info-footer">
                    <p>
                        <i class="fas fa-university"></i> Centro Pre Universitario
                    </p>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> Av. Dos de Mayo N° 960
                    </p>
                    <p>
                        <i class="fas fa-location-dot"></i> Puerto Maldonado, PERÚ
                    </p>
                </div>
                <div class="social-links-footer" style="margin-top: 25px;">
                    <h3 class="social-title"><i class="fas fa-share-alt"></i> Síguenos</h3>
                    <div class="social-buttons-grid">
                        <a href="https://facebook.com/cepreunamad" target="_blank" class="social-btn fb">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                        <a href="https://youtube.com/@cepreunamad" target="_blank" class="social-btn yt">
                            <i class="fab fa-youtube"></i> YouTube
                        </a>
                        <a href="https://instagram.com/cepreunamad" target="_blank" class="social-btn ig">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                        <a href="https://wa.me/51993110927" target="_blank" class="social-btn wa">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h3><i class="fas fa-bolt"></i> Enlaces Rápidos</h3>
                <ul>
                    <li><a href="{{ route('public.carreras.index') }}"><i class="fas fa-chevron-right"></i> Carreras Profesionales</a></li>
                    <li><a href="{{ route('resultados-examenes.public') }}"><i class="fas fa-chevron-right"></i> Resultados de Exámenes</a></li>
                    <li><a href="{{ route('login') }}"><i class="fas fa-chevron-right"></i> Portal de Estudiantes</a></li>
                    <li><a href="{{ route('login') }}"><i class="fas fa-chevron-right"></i> Portal de Docentes</a></li>
                    <li><a href="{{ route('boletines.index') }}"><i class="fas fa-chevron-right"></i> Boletines Académicos</a></li>
                </ul>
            </div>

            <!-- Admissions -->
            <div class="footer-column">
                <h3><i class="fas fa-graduation-cap"></i> Admisión</h3>
                <ul>
                    <li><a href="{{ route('public.vacantes') }}"><i class="fas fa-chevron-right"></i> Cuadro de Vacantes</a></li>
                    <li><a href="{{ route('public.cursos') }}"><i class="fas fa-chevron-right"></i> Cursos del Ciclo</a></li>
                    <li><a href="{{ route('home') }}#contacto"><i class="fas fa-chevron-right"></i> Requisitos de Postulación</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Cronograma de Pagos</a></li>
                </ul>
            </div>

            <!-- Location & Map -->
            <div class="footer-column">
                <h3><i class="fas fa-map-marked-alt"></i> Ubicación</h3>
                <div class="footer-map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3902.946115904646!2d-69.18664422530846!3d-12.590138987693902!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9173a11977f6b98b%3A0x67396a57833075d9!2sUNAMAD!5e0!3m2!1ses-419!2spe!4v1712497600000!5m2!1ses-419!2spe" 
                        width="100%" 
                        height="130" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <p class="map-label">Sede Central UNAMAD</p>
            </div>
        </div>

        <!-- Copyright -->
        <div class="copyright">
            <p>&copy; {{ date('Y') }} CEPRE UNAMAD. Todos los derechos reservados. | <span style="color: var(--magenta-unamad)">Excelencia Académica</span></p>
        </div>
    </div>
</footer>

