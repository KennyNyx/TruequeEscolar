// Sistema de calificación con estrellas
const ratingInputs = document.querySelectorAll('input[name="calificacion"]');
const ratingText = document.getElementById('rating-text');

const ratingTexts = {
    1: '⭐ Muy Malo',
    2: '⭐⭐ Malo',
    3: '⭐⭐⭐ Regular',
    4: '⭐⭐⭐⭐ Bueno',
    5: '⭐⭐⭐⭐⭐ Excelente'
};

ratingInputs.forEach(input => {
    input.addEventListener('change', function() {
        ratingText.textContent = ratingTexts[this.value];
    });
});

// Vista previa de imágenes
const imageInput = document.getElementById('imagenes');
const imagePreview = document.getElementById('imagePreview');
let selectedFiles = [];

if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        if (files.length > 5) {
            alert('Solo puedes subir máximo 5 imágenes');
            return;
        }
        
        selectedFiles = files;
        imagePreview.innerHTML = '';
        
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const container = document.createElement('div');
                    container.className = 'image-preview-container';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '×';
                    removeBtn.type = 'button';
                    removeBtn.onclick = function() {
                        selectedFiles.splice(index, 1);
                        container.remove();
                        updateFileInput();
                    };
                    
                    container.appendChild(img);
                    container.appendChild(removeBtn);
                    imagePreview.appendChild(container);
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}
/**
 * Actualiza el input file (type="file") con los archivos restantes en selectedFiles
 *
 * Usa DataTransfer API para manipular el objeto FileList de forma segura.
 *
 * /Recibe: Nada
 * /Devuelve: Nada
 */
function updateFileInput() {
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    imageInput.files = dt.files;
}

// Validación del formulario
const formResena = document.getElementById('formResena');
if (formResena) {
    formResena.addEventListener('submit', function(e) {
        const calificacion = document.querySelector('input[name="calificacion"]:checked');
        const comentario = document.getElementById('comentario').value.trim();
        const correoEvaluado = document.getElementById('correo_evaluado').value.trim();
        
        if (!calificacion) {
            e.preventDefault();
            alert('Por favor selecciona una calificación');
            return false;
        }
        
        if (comentario.length < 10) {
            e.preventDefault();
            alert('Por favor escribe un comentario más detallado (mínimo 10 caracteres)');
            return false;
        }

        if (!correoEvaluado.includes('@upemor.edu.mx')) {
            e.preventDefault();
            alert('Debes usar un correo institucional (@upemor.edu.mx)');
            return false;
        }
    });
}