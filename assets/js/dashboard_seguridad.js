// Función de búsqueda en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const filtroFecha = document.getElementById('filtro-fecha');
    const tabla = document.querySelector('.custom-table tbody');
    
    if (buscador && tabla) {
        buscador.addEventListener('input', function() {
            const termino = this.value.toLowerCase();
            const filas = tabla.getElementsByTagName('tr');
            
            Array.from(filas).forEach(fila => {
                const nombre = fila.cells[0].textContent.toLowerCase();
                const cedula = fila.cells[1].textContent.toLowerCase();
                
                if (nombre.includes(termino) || cedula.includes(termino)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
    
    // Función de filtro por fecha
    if (filtroFecha) {
        filtroFecha.addEventListener('change', function() {
            const fechaSeleccionada = this.value;
            window.location.href = `?fecha=${fechaSeleccionada}`;
        });
    }
});

// --- Lógica del Modal de Entrada y Webcam ---
let streamMedia = null;

function marcarEntrada(id) {
    document.getElementById('visit-id-modal').value = id;
    document.getElementById('modal-entrada').style.display = 'block';
    iniciarCamara();
}

function cerrarModal() {
    document.getElementById('modal-entrada').style.display = 'none';
    if (streamMedia) {
        streamMedia.getTracks().forEach(track => track.stop());
    }
    reintentarFoto();
}

async function iniciarCamara() {
    try {
        streamMedia = await navigator.mediaDevices.getUserMedia({ video: true });
        const video = document.getElementById('webcam');
        video.srcObject = streamMedia;
    } catch (err) {
        console.error("Error al acceder a la cámara:", err);
        alert("No se pudo acceder a la cámara. Verifique los permisos.");
    }
}

function capturarFoto() {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('photo-canvas');
    const preview = document.getElementById('photo-preview');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    const dataURL = canvas.toDataURL('image/jpeg');
    preview.src = dataURL;
    
    video.style.display = 'none';
    document.getElementById('photo-preview-container').style.display = 'block';
    document.getElementById('btn-foto').style.display = 'none';
    document.getElementById('btn-reintentar').style.display = 'block';
}

function reintentarFoto() {
    document.getElementById('webcam').style.display = 'block';
    document.getElementById('photo-preview-container').style.display = 'none';
    document.getElementById('btn-foto').style.display = 'block';
    document.getElementById('btn-reintentar').style.display = 'none';
}

function confirmarEntrada() {
    const id = document.getElementById('visit-id-modal').value;
    const tag = document.getElementById('tag_iot').value;
    const canvas = document.getElementById('photo-canvas');
    const foto = canvas.toDataURL('image/jpeg');

    const btn = document.getElementById('btn-confirmar-entrada');
    btn.disabled = true;
    btn.innerText = 'Procesando...';

    fetch('../api/visitas/actualizar_estado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&estado=en_edificio&tag_iot=${encodeURIComponent(tag)}&foto=${encodeURIComponent(foto)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Entrada registrada correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + (data.error || 'No se pudo registrar la entrada'));
            btn.disabled = false;
            btn.innerText = '✅ Confirmar Entrada';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
        btn.disabled = false;
        btn.innerText = '✅ Confirmar Entrada';
    });
}

// --- Lógica de Ver Detalles ---
function verDetalles(id) {
    abrirModal('modal-detalles');
    document.getElementById('detalles-loading').style.display = 'block';
    document.getElementById('detalles-body').style.display = 'none';

    fetch(`../api/visitas/get_detalle.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const v = res.data;
                document.getElementById('detalles-loading').style.display = 'none';
                document.getElementById('detalles-body').style.display = 'block';

                document.getElementById('detalles-nombre').innerText = v.visitante_nombre;
                document.getElementById('detalles-cedula').innerText = 'ID: ' + v.visitante_cedula;
                document.getElementById('detalles-residente').innerText = v.residente_nombre || 'No asignado';
                document.getElementById('detalles-ubicacion').innerText = v.apto ? `Torre ${v.torre} - Apt ${v.apto}` : 'N/A';
                document.getElementById('detalles-entrada').innerText = v.fecha_entrada_real || '--:--';
                document.getElementById('detalles-salida').innerText = v.fecha_salida || '--:--';
                document.getElementById('detalles-notas').innerText = v.notas || 'Sin observaciones.';
                
                // Foto
                const img = document.getElementById('detalles-foto');
                img.src = v.foto_url ? '../' + v.foto_url : '../assets/img/default-user.png';
                
                // Estado badge
                const status = document.getElementById('detalles-estado');
                status.innerText = v.estado.toUpperCase();
                status.className = 'badge-status ' + (v.estado === 'en_edificio' ? 'badge-edificio' : (v.estado === 'pendiente' ? 'badge-pendiente' : 'badge-finalizada'));
            } else {
                alert('No se pudieron cargar los detalles');
                cerrarModal('modal-detalles');
            }
        });
}

function marcarSalida(id) {
    if (confirm('¿Confirmar marcado de salida?')) {
        fetch('../api/visitas/actualizar_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&estado=finalizada`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + data.error);
        });
    }
}
