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

// Función para marcar entrada
function marcarEntrada(id) {
    if (confirm('¿Confirmar marcado de entrada para esta visita?')) {
        fetch('../api/visitas/actualizar_estado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&estado=en_edificio`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Entrada marcada exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'No se pudo marcar la entrada'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al marcar entrada');
        });
    }
}

// Función para marcar salida
function marcarSalida(id) {
    if (confirm('¿Confirmar marcado de salida para esta visita?')) {
        fetch('../api/visitas/actualizar_estado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&estado=finalizada`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Salida marcada exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'No se pudo marcar la salida'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al marcar salida');
        });
    }
}
