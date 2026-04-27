{{-- resources/views/partials/cepre/modal-detalles-ciclo.blade.php --}}
<div class="modal fade premium-modal-v3" id="modalDetallesCicloV3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close-premium-v3" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="notebook-detail-container">
                    <div class="row align-items-stretch w-100 m-0">
                        <!-- Columna Izquierda: Imagen y Beneficios -->
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <div class="detail-image-box-v3">
                                <img id="md-ciclo-imagen-v3" src="" alt="Imagen Ciclo">
                                <div style="position: absolute; bottom: 20px; right: 20px; background: rgba(236, 0, 140, 0.9); color: white; padding: 8px 20px; border-radius: 50px; font-weight: 800; font-size: 12px; letter-spacing: 1px; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                                    PROYECTO ACADÉMICO
                                </div>
                            </div>
                            
                            <!-- Nueva Sección: Beneficios -->
                            <div class="mt-4 p-4" style="background: #f8fafc; border-radius: 25px; border: 1px solid rgba(0,0,0,0.03);">
                                <h6 style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 15px; letter-spacing: 1px;">
                                    <i class="fas fa-star" style="color: #f59e0b;"></i> BENEFICIOS EXCLUSIVOS
                                </h6>
                                <ul style="list-style: none; padding: 0; margin: 0; font-size: 14px; color: #64748b; font-weight: 500;">
                                    <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-check-circle" style="color: #8cc63f;"></i> Plana docente especializada
                                    </li>
                                    <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-check-circle" style="color: #8cc63f;"></i> Material didáctico actualizado
                                    </li>
                                    <li style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-check-circle" style="color: #8cc63f;"></i> Exámenes oficiales presenciales
                                    </li>
                                    <li style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-check-circle" style="color: #8cc63f;"></i> Orientación vocacional continua
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Columna Derecha: Información -->
                        <div class="col-lg-7">
                            <div class="detail-content-box ps-lg-5">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <span style="width: 30px; height: 2px; background: #ec008c;"></span>
                                    <h6 class="premium-tagline text-start m-0" style="color: #ec008c; font-weight: 800; font-size: 13px; letter-spacing: 2px; text-transform: uppercase;">DETALLES DEL PROGRAMA</h6>
                                </div>
                                
                                <h2 id="md-ciclo-nombre-v3" style="font-size: clamp(28px, 4vw, 46px); font-weight: 900; color: #0f172a; margin-bottom: 15px; line-height: 1; letter-spacing: -1px;">Cargando...</h2>
                                
                                <p id="md-ciclo-descripcion-v3" style="font-size: 17px; color: #64748b; line-height: 1.7; margin-bottom: 30px; font-weight: 450;">Explora las ventajas competitivas de nuestro ciclo académico diseñado para tu éxito profesional.</p>
                                
                                <!-- Grid de Fechas con Glassmorphism -->
                                <div class="row g-3 mb-5">
                                    <div class="col-sm-6">
                                        <div class="info-card-premium">
                                            <div style="width: 45px; height: 45px; background: rgba(0, 174, 239, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #00aeef; margin-bottom: 15px; font-size: 20px;">
                                                <i class="fas fa-calendar-day"></i>
                                            </div>
                                            <span style="display: block; font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Inicio de Clases</span>
                                            <strong id="md-ciclo-inicio-v3" style="color: #0f172a; font-size: 18px; font-weight: 800;">-</strong>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-card-premium">
                                            <div style="width: 45px; height: 45px; background: rgba(140, 198, 63, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #8cc63f; margin-bottom: 15px; font-size: 20px;">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <span style="display: block; font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Cierre de Ciclo</span>
                                            <strong id="md-ciclo-fin-v3" style="color: #0f172a; font-size: 18px; font-weight: 800;">-</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cronograma con Iconos -->
                                <div class="mt-4">
                                    <h5 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 25px; letter-spacing: 1px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-route" style="color: #ec008c;"></i> CRONOGRAMA DE EXÁMENES
                                    </h5>
                                    <div style="display: flex; justify-content: space-between; background: #f8fafc; padding: 35px 20px; border-radius: 30px; position: relative; border: 1px solid rgba(0,0,0,0.03); box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);">
                                        <!-- Línea de conexión animada -->
                                        <div style="position: absolute; top: 58px; left: 15%; right: 15%; height: 4px; background: #e2e8f0; z-index: 1; border-radius: 10px;">
                                            <div style="width: 100%; height: 100%; background: linear-gradient(90deg, #ec008c, #00aeef, #8cc63f); border-radius: 10px; opacity: 0.3;"></div>
                                        </div>
                                        
                                        <div class="exam-step-v3">
                                            <div class="step-number-v3"><i class="fas fa-file-signature"></i></div>
                                            <div style="background: white; padding: 10px; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); margin-top: 5px;">
                                                <span id="md-ciclo-exam1-v3" style="display: block; font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">-</span>
                                                <span style="font-size: 10px; color: #ec008c; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">1er EXAMEN</span>
                                            </div>
                                        </div>
                                        <div class="exam-step-v3">
                                            <div class="step-number-v3" style="background: linear-gradient(135deg, #00aeef, #00d2ff); box-shadow: 0 10px 20px rgba(0, 174, 239, 0.3);"><i class="fas fa-pen-fancy"></i></div>
                                            <div style="background: white; padding: 10px; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); margin-top: 5px;">
                                                <span id="md-ciclo-exam2-v3" style="display: block; font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">-</span>
                                                <span style="font-size: 10px; color: #00aeef; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">2do EXAMEN</span>
                                            </div>
                                        </div>
                                        <div class="exam-step-v3">
                                            <div class="step-number-v3" style="background: linear-gradient(135deg, #8cc63f, #a4c639); box-shadow: 0 10px 20px rgba(140, 198, 63, 0.3);"><i class="fas fa-graduation-cap"></i></div>
                                            <div style="background: white; padding: 10px; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); margin-top: 5px;">
                                                <span id="md-ciclo-exam3-v3" style="display: block; font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 2px;">-</span>
                                                <span style="font-size: 10px; color: #8cc63f; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">3er EXAMEN</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón CTA -->
                                <div class="mt-5">
                                    <button class="btn btn-action-premium-v3 w-100 d-flex align-items-center justify-content-center gap-3" onclick="openPostulacionModal()">
                                        <i class="fas fa-user-plus"></i>
                                        <span>INICIAR INSCRIPCIÓN AHORA</span>
                                        <i class="fas fa-chevron-right" style="font-size: 12px; opacity: 0.7;"></i>
                                    </button>
                                    <p style="text-align: center; font-size: 12px; color: #94a3b8; margin-top: 15px; font-weight: 600;">
                                        <i class="fas fa-shield-alt"></i> Registro 100% Seguro y Validado
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openCicloDetails(data) {
        const ciclo = JSON.parse(data);
        
        // Función para traducir fechas de inglés a español
        const traducirFecha = (fechaStr) => {
            if(!fechaStr) return '-';
            const meses = {
                'Jan': 'Enero', 'Feb': 'Febrero', 'Mar': 'Marzo', 'Apr': 'Abril',
                'May': 'Mayo', 'Jun': 'Junio', 'Jul': 'Julio', 'Aug': 'Agosto',
                'Sep': 'Septiembre', 'Oct': 'Octubre', 'Nov': 'Noviembre', 'Dec': 'Diciembre'
            };
            let traducida = fechaStr;
            Object.keys(meses).forEach(key => {
                traducida = traducida.replace(key, meses[key]);
            });
            return traducida.replace(',', ' de'); // Ejemplo: 04 Mayo de 2026
        };

        document.getElementById('md-ciclo-nombre-v3').innerText = ciclo.nombre;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = ciclo.descripcion || 'Embárcate en un viaje de aprendizaje diseñado para superar tus límites y alcanzar la excelencia académica.';
        document.getElementById('md-ciclo-descripcion-v3').innerText = tempDiv.textContent || tempDiv.innerText;

        document.getElementById('md-ciclo-inicio-v3').innerText = traducirFecha(ciclo.fecha_inicio_fmt);
        document.getElementById('md-ciclo-fin-v3').innerText = traducirFecha(ciclo.fecha_fin_fmt);
        document.getElementById('md-ciclo-imagen-v3').src = ciclo.imagen;
        
        document.getElementById('md-ciclo-exam1-v3').innerText = traducirFecha(ciclo.exam1);
        document.getElementById('md-ciclo-exam2-v3').innerText = traducirFecha(ciclo.exam2);
        document.getElementById('md-ciclo-exam3-v3').innerText = traducirFecha(ciclo.exam3);
        
        var myModal = new bootstrap.Modal(document.getElementById('modalDetallesCicloV3'));
        myModal.show();
    }
</script>
