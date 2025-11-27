// Variables globales
let selectedImages = [];
let currentEditingMessageId = null;

/**
 * Ejecuta todas las inicializaciones cuando el DOM está listo
 *
 * - Configura event listeners
 * - Inicia intervalos de actualización
 * - Prepara elementos del chat
 */
$(document).ready(function () {
    console.log('Chat JS cargado correctamente');
    
    // Actualizar cada 10s
    setInterval(function () {
        updateUserList();
        updateUnreadMessageCount();
    }, 10000);

    // Actualizar cada 1s
    setInterval(function () {
        showTypingStatus();
        updateUserChat();
    }, 1000);

    // Seleccionar contacto
    $(document).on('click', '.contact', function () {
        console.log('Contacto seleccionado');
        $('.contact').removeClass('active');
        $(this).addClass('active');
        var to_user_id = $(this).data('touserid');
        console.log('ID del contacto:', to_user_id);
        showUserChat(to_user_id);
    });

    // ==========================================
    // MANEJO DE IMÁGENES AL ENVIAR
    // ==========================================
    
    // Preview de imágenes seleccionadas
    $('#chatImageInput').on('change', function(e) {
        console.log('Imágenes seleccionadas');
        const files = e.target.files;
        console.log('Número de archivos:', files.length);
        
        selectedImages = Array.from(files);
        $('#imagePreviewContainer').empty();
        
        if (files.length > 10) {
            alert('Máximo 10 imágenes permitidas');
            this.value = '';
            selectedImages = [];
            return;
        }
        
        if (files.length > 0) {
            $('#imagePreviewContainer').show();
            
            selectedImages.forEach((file, index) => {
                console.log('Procesando archivo:', file.name);
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewHtml = `
                        <div class="preview-image-item" data-index="${index}">
                            <img src="${e.target.result}" alt="Preview">
                            <button class="remove-preview" type="button" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    $('#imagePreviewContainer').append(previewHtml);
                };
                reader.readAsDataURL(file);
            });
        } else {
            $('#imagePreviewContainer').hide();
        }
    });

    // Remover preview de imagen
    $(document).on('click', '.remove-preview', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        console.log('Removiendo imagen en índice:', index);
        removePreviewImage(index);
    });

    // ==========================================
    // ACCIONES DE MENSAJE
    // ==========================================

    // Mostrar menú de acciones al hacer clic en la burbuja
    $(document).on("click", 'li.sent .message-bubble', function (e) {
        e.stopPropagation();
        console.log('Click en burbuja de mensaje');
        
        // Cerrar todos los menús abiertos
        $('.message-actions').removeClass('show').hide();
        
        // Mostrar el menú de este mensaje
        var $currentActions = $(this).closest('.message-content').find('.message-actions');
        $currentActions.addClass('show').show();
    });

    // Ocultar menús al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.message-content').length) {
            $('.message-actions').removeClass('show').hide();
        }
    });

    $(document).on('click', '.message-actions, .message-edit-form', function(e) {
        e.stopPropagation();
    });

    // ==========================================
    // EDITAR MENSAJE
    // ==========================================
    
    $(document).on('click', '.btn-edit-msg', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Editando mensaje');

        var $parentContent = $(this).closest('.message-content');
        var messageId = $(this).data('message-id');
        
        currentEditingMessageId = messageId;
        
        // Ocultar burbuja y acciones
        $parentContent.find('.message-bubble').hide();
        $parentContent.find('.message-actions').removeClass('show').hide();
        
        // Mostrar formulario
        $parentContent.find('.message-edit-form').addClass('show').show();
    });

    // Manejar selección de nuevas imágenes en edición
    $(document).on('change', '.edit-image-input', function(e) {
        const files = e.target.files;
        const messageId = $(this).data('message-id');
        const $previewContainer = $('#edit-preview-' + messageId);
        
        // Contar imágenes actuales
        const currentImagesCount = $previewContainer.find('.edit-image-item').length;
        
        if (currentImagesCount + files.length > 10) {
            alert('Máximo 10 imágenes en total');
            this.value = '';
            return;
        }
        
        // Agregar nuevas imágenes al preview
        Array.from(files).forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const newImageHtml = `
                    <div class="edit-image-item" data-new-image="true" data-file-index="${idx}">
                        <img src="${e.target.result}" alt="Nueva imagen">
                        <button type="button" class="btn-remove-edit-image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                $previewContainer.append(newImageHtml);
            };
            reader.readAsDataURL(file);
        });
    });

    // Remover imagen en edición
    $(document).on('click', '.btn-remove-edit-image', function(e) {
        e.preventDefault();
        $(this).closest('.edit-image-item').remove();
    });

    // Guardar edición
    $(document).on('click', '.btn-save-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $button = $(this);
        var messageId = $button.data('message-id');
        var $form = $button.closest('.message-edit-form');
        var $input = $form.find('.edit-message-input');
        var newMessage = $input.val().trim();

        // Recopilar imágenes existentes que se mantienen
        var existingImages = [];
        $form.find('.edit-image-item:not([data-new-image])').each(function() {
            existingImages.push($(this).data('image-url'));
        });

        // Preparar FormData
        var formData = new FormData();
        formData.append('action', 'edit_message');
        formData.append('message_id', messageId);
        formData.append('new_message', newMessage);
        formData.append('existing_images', JSON.stringify(existingImages));

        // Agregar nuevas imágenes
        var $imageInput = $form.find('.edit-image-input')[0];
        if ($imageInput && $imageInput.files.length > 0) {
            for (let i = 0; i < $imageInput.files.length; i++) {
                formData.append('edit_images[]', $imageInput.files[i]);
            }
        }

        $button.prop('disabled', true);

        $.ajax({
            url: "index.php?action=chat_ajax",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('.message-edit-form').removeClass('show').hide();
                    $('.message-bubble').show();
                    updateUserChat(true);
                    currentEditingMessageId = null;
                } else {
                    alert('Error al editar: ' + (response.error || 'Desconocido'));
                    $button.prop('disabled', false);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                alert('Error de conexión al guardar.');
                $button.prop('disabled', false);
            }
        });
    });

    // Cancelar edición
    $(document).on('click', '.btn-cancel-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $parentContent = $(this).closest('.message-content');
        $parentContent.find('.message-edit-form').removeClass('show').hide();
        $parentContent.find('.message-bubble').show();
        currentEditingMessageId = null;
    });

    // ==========================================
    // ELIMINAR MENSAJE
    // ==========================================
    
    $(document).on("click", '.btn-delete-msg', function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Eliminando mensaje');
        
        var $li = $(this).closest('li.sent');
        var message_id = $li.data('msg-id');

        if (!confirm('¿Estás seguro de eliminar este mensaje?')) {
            return;
        }

        $.ajax({
            url: "index.php?action=chat_ajax",
            method: "POST",
            dataType: "json",
            data: {
                message_id: message_id,
                action: 'delete_message'
            },
            success: function (response) {
                console.log('Respuesta eliminar:', response);
                if (response.success) {
                    updateUserChat(true);
                } else {
                    alert('Error al borrar el mensaje.');
                }
            }
        });
    });

    // ==========================================
    // ENVIAR MENSAJE
    // ==========================================
    
    $(document).on("click", '.submit', function (e) {
        e.preventDefault();
        console.log('Click en botón enviar');
        var to_user_id = $('li.contact.active').data('touserid');
        console.log('Enviando a usuario:', to_user_id);
        if (!to_user_id) {
            alert('Selecciona un usuario para chatear.');
            return;
        }
        sendMessage(to_user_id);
    });

    $(document).on('keypress', '.message-input input.chatMessage', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            console.log('Enter presionado');
            var to_user_id = $('li.contact.active').data('touserid');
            if (!to_user_id) return;
            sendMessage(to_user_id);
            return false;
        }
    });

    // ==========================================
    // ESTADO ESCRIBIENDO
    // ==========================================
    
    $(document).on('focus', '.chatMessage', function () {
        $.ajax({
            url: "index.php?action=chat_ajax",
            method: "POST",
            data: { is_type: 'yes', action: 'update_typing_status' }
        });
    });

    $(document).on('blur', '.chatMessage', function () {
        $.ajax({
            url: "index.php?action=chat_ajax",
            method: "POST",
            data: { is_type: 'no', action: 'update_typing_status' }
        });
    });
});

/* ===========================
   FUNCIONES AUXILIARES
   =========================== */

function removePreviewImage(index) {
    $(`.preview-image-item[data-index="${index}"]`).remove();
    selectedImages.splice(index, 1);
    
    // Actualizar el input file
    const dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    document.getElementById('chatImageInput').files = dt.files;
    
    if (selectedImages.length === 0) {
        $('#imagePreviewContainer').hide();
        $('#chatImageInput').val('');
    }
}

/* ===========================
   FUNCIONES AJAX
   =========================== */

function updateUserList() {
    $.ajax({
        url: "index.php?action=chat_ajax",
        method: "POST",
        dataType: "json",
        data: { action: 'update_user_list' },
        success: function (response) {
            if (!response.profileHTML) return;
            var obj = response.profileHTML;
            Object.keys(obj).forEach(function (key) {
                var u = obj[key];
                var statusElem = $("#status_" + u.userid);
                if (statusElem.length) {
                    if (u.online == 1 && !statusElem.hasClass('online')) {
                        statusElem.addClass('online');
                    } else if (u.online == 0) {
                        statusElem.removeClass('online');
                    }
                }
            });
        }
    });
}

function sendMessage(to_user_id) {
    console.log('=== ENVIANDO MENSAJE ===');
    var message = $(".chatMessage").val().trim();
    console.log('Mensaje:', message);
    console.log('Imágenes seleccionadas:', selectedImages.length);
    
    var formData = new FormData();
    
    formData.append('to_user_id', to_user_id);
    formData.append('chat_message', message);
    formData.append('action', 'insert_chat');
    
    // Agregar imágenes si hay
    if (selectedImages.length > 0) {
        console.log('Agregando imágenes al FormData');
        selectedImages.forEach((file, index) => {
            console.log(`Imagen ${index}:`, file.name, file.size, 'bytes');
            formData.append('chat_images[]', file);
        });
    }
    
    // Debug: mostrar contenido del FormData
    console.log('Contenido del FormData:');
    for (let pair of formData.entries()) {
        console.log(pair[0], pair[1]);
    }
    
    // Validar que haya al menos mensaje o imágenes
    if (message === '' && selectedImages.length === 0) {
        alert('Escribe un mensaje o selecciona imágenes');
        return false;
    }
    
    console.log('Enviando petición AJAX...');
    
    $.ajax({
        url: "index.php?action=chat_ajax",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (response) {
            console.log('Respuesta del servidor:', response);
            
            if (response.conversation) {
                console.log('Mensaje enviado correctamente');
                $('#conversation').html(response.conversation);
                scrollToBottom();
                
                // Limpiar inputs DESPUÉS de éxito
                $('.chatMessage').val('');
                $('#chatImageInput').val('');
                $('#imagePreviewContainer').hide().empty();
                selectedImages = [];
            } else if (response.error) {
                console.error('Error del servidor:', response.error);
                alert('Error: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Respuesta:', xhr.responseText);
            alert('Error al enviar el mensaje. Revisa la consola.');
        }
    });
}

function showUserChat(to_user_id) {
    console.log('Cargando chat con usuario:', to_user_id);
    
    $.ajax({
        url: "index.php?action=chat_ajax",
        method: "POST",
        dataType: "json",
        data: { to_user_id: to_user_id, action: 'show_chat' },
        success: function (response) {
            console.log('Respuesta show_chat:', response);
            
            if (response.userSection) {
                $('#userSection').html(response.userSection);
            }
            if (response.conversation) {
                $('#conversation').html(response.conversation);
                scrollToBottom();
            }
            $('#unread_' + to_user_id).html('');
        },
        error: function(xhr, status, error) {
            console.error('Error en AJAX:', xhr.responseText);
            alert('Error al cargar el chat');
        }
    });
}

function updateUserChat(forceRefresh = false) {
    // No actualizar si está editando
    if (forceRefresh === false && currentEditingMessageId !== null) {
        return;
    }
    
    if (forceRefresh === false && 
        ($('.message-edit-form').is(':visible') || $('.message-actions').is(':visible'))) {
        return;
    }
    
    var $activeContact = $('li.contact.active');
    if (!$activeContact.length) {
        return;
    }
    
    var to_user_id = $activeContact.data('touserid');

    $.ajax({
        url: "index.php?action=chat_ajax",
        method: "POST",
        dataType: "json",
        data: {
            to_user_id: to_user_id,
            action: 'update_user_chat'
        },
        success: function (response) {
            if (response.conversation) {
                var $conversation = $('#conversation');
                var isScrolledToBottom = $conversation[0].scrollHeight - $conversation[0].clientHeight <= $conversation.scrollTop() + 1;
                
                $('#conversation').html(response.conversation);

                if(isScrolledToBottom) {
                    scrollToBottom();
                }
            }
        }
    });
}

function updateUnreadMessageCount() {
    $('li.contact').each(function () {
        var li = $(this);
        if (!li.hasClass('active')) {
            var to_user_id = li.attr('data-touserid');
            $.ajax({
                url: "index.php?action=chat_ajax",
                method: "POST",
                dataType: "json",
                data: { to_user_id: to_user_id, action: 'update_unread_message' },
                success: function (response) {
                    if (typeof response.count !== 'undefined') {
                        $('#unread_' + to_user_id).html(
                            response.count > 0 ? response.count : ''
                        );
                    }
                }
            });
        }
    });
}

function showTypingStatus() {
    var active = $('li.contact.active');
    if (!active.length) return;
    var to_user_id = active.attr('data-touserid');

    $.ajax({
        url: "index.php?action=chat_ajax",
        method: "POST",
        dataType: "json",
        data: { to_user_id: to_user_id, action: 'show_typing_status' },
        success: function (response) {
            if (typeof response.message !== 'undefined') {
                $('#isTyping_' + to_user_id).html(response.message);
            }
        }
    });
}

function scrollToBottom() {
    var $conversation = $('#conversation');
    $conversation.scrollTop($conversation[0].scrollHeight);
}