<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: index.php?action=login");
    exit();
}

$nombre_usuario = $_SESSION['user_nombre'];


$imagenes_actuales = json_decode($resena['imagenes'] ?? '[]', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reseña - Trueques UPEMOR</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="bootstrap/css/style.css">
</head>
<body class="dashboard-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Trueques Escolares UPEMOR</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php?action=vistaAlumnos"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="index.php?action=catalogo"><i class="fas fa-book-open me-2"></i> Catálogo</a>
            <a href="index.php?action=agregarProducto"><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
            <a href="index.php?action=reuniones"><i class="fas fa-users me-2"></i> Reuniones</a>
            <a href="index.php?action=chat"><i class="fas fa-comments me-2"></i> Chat</a>
            <a href="index.php?action=resenas" class="active"><i class="fas fa-star me-2"></i> Reseñas</a>
        </nav>
        <div class="sidebar-footer">
            <a href="index.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="dashboard-header">
            <img src="img/upemor.png" alt="Logo Upemor" class="header-logo">
            <h1 class="header-title">Universidad Politécnica del Estado de Morelos</h1>
            <div class="header-user">
                <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
                <i class="fas fa-user-circle ms-2"></i>
            </div>
        </header>

        <main class="dashboard-content p-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        
                        <a href="index.php?action=resenas" class="btn btn-secondary mb-3">
                            <i class="fas fa-arrow-left me-2"></i> Volver
                        </a>

                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>Editar Reseña
                                </h4>
                            </div>
                            
                            <div class="card-body p-4">
                                
                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php 
                                        $errores = [
                                            'campos_vacios' => 'Por favor completa todos los campos obligatorios.',
                                            'calificacion_invalida' => 'La calificación debe ser entre 1 y 5 estrellas.',
                                            'error_db' => 'Error al actualizar la reseña. Intenta nuevamente.'
                                        ];
                                        echo $errores[$_GET['error']] ?? 'Error desconocido.';
                                        ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Nota:</strong> Estás editando tu reseña. Los cambios se guardarán al presionar "Guardar Cambios".
                                </div>

                                <form action="index.php?action=actualizar_resena" method="POST" enctype="multipart/form-data" id="formEditarResena">
                                    <input type="hidden" name="id_resena" value="<?= $resena['id_resena'] ?>">
                                    
                                  
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-user-check me-2"></i>Alumno Evaluado
                                        </h5>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Nombre del Alumno:</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= htmlspecialchars($resena['nombre_evaluado']) ?>" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Correo:</label>
                                            <input type="email" class="form-control" 
                                                   value="<?= htmlspecialchars($resena['correo_evaluado']) ?>" readonly>
                                            <small class="form-text text-muted">
                                                No puedes cambiar el alumno evaluado.
                                            </small>
                                        </div>
                                    </div>

                                    <hr>

                                    
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-box me-2"></i>Información del Producto
                                        </h5>
                                        <div class="form-group mb-3">
                                            <label for="objeto_evaluado" class="form-label">
                                                <i class="fas fa-box me-1"></i>Producto/Objeto Recibido: <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="objeto_evaluado" 
                                                   name="objeto_evaluado" 
                                                   required 
                                                   value="<?= htmlspecialchars($resena['objeto_evaluado']) ?>"
                                                   placeholder="Ej: Mouse Gamer Logitech">
                                        </div>
                                    </div>

                                    <hr>

                                   
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-star me-2"></i>Calificación <span class="text-danger">*</span>
                                        </label>
                                        <div class="text-center py-3">
                                            <div class="star-rating">
                                                <input type="radio" name="calificacion" id="star5" value="5" <?= $resena['calificacion'] == 5 ? 'checked' : '' ?> required>
                                                <label for="star5"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star4" value="4" <?= $resena['calificacion'] == 4 ? 'checked' : '' ?>>
                                                <label for="star4"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star3" value="3" <?= $resena['calificacion'] == 3 ? 'checked' : '' ?>>
                                                <label for="star3"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star2" value="2" <?= $resena['calificacion'] == 2 ? 'checked' : '' ?>>
                                                <label for="star2"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star1" value="1" <?= $resena['calificacion'] == 1 ? 'checked' : '' ?>>
                                                <label for="star1"><i class="fas fa-star"></i></label>
                                            </div>
                                            <p class="text-muted mt-2" id="rating-text">
                                                <?php
                                                $textos = [
                                                    1 => '⭐ Muy Malo',
                                                    2 => '⭐⭐ Malo',
                                                    3 => '⭐⭐⭐ Regular',
                                                    4 => '⭐⭐⭐⭐ Bueno',
                                                    5 => '⭐⭐⭐⭐⭐ Excelente'
                                                ];
                                                echo $textos[$resena['calificacion']] ?? 'Selecciona una calificación';
                                                ?>
                                            </p>
                                        </div>
                                    </div>

                                   
                                    <div class="mb-4">
                                        <label for="comentario" class="form-label fw-bold">
                                            <i class="fas fa-comment me-2"></i>Tu Opinión <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" 
                                                  id="comentario" 
                                                  name="comentario" 
                                                  rows="5" 
                                                  required
                                                  placeholder="Comparte tu experiencia sobre el producto recibido, estado, funcionalidad, etc..."><?= htmlspecialchars($resena['comentario']) ?></textarea>
                                        <small class="form-text text-muted">
                                            Describe el estado del producto y tu experiencia general.
                                        </small>
                                    </div>

                                  
                                    <?php if (!empty($imagenes_actuales)): ?>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-images me-2"></i>Imágenes Actuales
                                            </label>
                                            <div class="preview-images" id="currentImages">
                                                <?php foreach ($imagenes_actuales as $index => $img): ?>
                                                    <div class="image-preview-container" data-image="<?= htmlspecialchars($img) ?>">
                                                        <img src="uploads/resenas/<?= htmlspecialchars($img) ?>" 
                                                             alt="Imagen actual"
                                                             onerror="this.parentElement.style.display='none'">
                                                        <button type="button" class="remove-image" onclick="removeCurrentImage(this, '<?= htmlspecialchars($img) ?>')">
                                                            ×
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <small class="form-text text-muted">
                                                Puedes eliminar imágenes haciendo clic en la "×"
                                            </small>
                                            <input type="hidden" name="imagenes_eliminar" id="imagenesEliminar" value="">
                                        </div>
                                    <?php endif; ?>

                                   
                                    <div class="mb-4">
                                        <label for="imagenes" class="form-label fw-bold">
                                            <i class="fas fa-camera me-2"></i>Agregar Más Fotos (Opcional)
                                        </label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="imagenes" 
                                               name="imagenes[]" 
                                               accept="image/*" 
                                               multiple>
                                        <small class="form-text text-muted">
                                            Puedes subir hasta 5 imágenes nuevas (JPG, PNG, GIF)
                                        </small>
                                        
                                        <!-- Vista previa de nuevas imágenes -->
                                        <div class="preview-images" id="imagePreview"></div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Botones -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i>Guardar Cambios
                                        </button>
                                        <a href="index.php?action=resenas" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </a>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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

        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
        }

        
        let imagenesAEliminar = [];

        function removeCurrentImage(button, imageName) {
            if (confirm('¿Estás seguro de eliminar esta imagen?')) {
                
                imagenesAEliminar.push(imageName);
                document.getElementById('imagenesEliminar').value = JSON.stringify(imagenesAEliminar);
                
              
                button.parentElement.remove();
            }
        }

        
        const formEditarResena = document.getElementById('formEditarResena');
        if (formEditarResena) {
            formEditarResena.addEventListener('submit', function(e) {
                const calificacion = document.querySelector('input[name="calificacion"]:checked');
                const comentario = document.getElementById('comentario').value.trim();
                const objetoEvaluado = document.getElementById('objeto_evaluado').value.trim();
                
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

                if (objetoEvaluado.length < 3) {
                    e.preventDefault();
                    alert('Por favor ingresa el nombre del producto evaluado');
                    return false;
                }
            });
        }
    </script>
</body>
</html>