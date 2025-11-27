<?php
class ProductoModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene todos los productos ordenados por fecha (del más reciente al más antiguo)
     *  Solo muestra productos NO eliminados (eliminado = 0)
     * Incluye información del alumno propietario (nombre, carrera, turno).
     *
     * /Recibe: Nada.
     * /Devuelve: array de productos o [] en error
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT p.*, a.nombre AS nombre_alumno, a.carrera, a.turno 
                    FROM objetos p 
                    JOIN alumnos a ON p.ID = a.ID 
                    WHERE p.eliminado = 0
                    ORDER BY p.fecha_publicacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un producto específico por su ID
     *
     * /Recibe: int $id (id_objetos)
     * /Devuelve: array|false
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM objetos WHERE id_objetos = :id AND eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Inserta un nuevo producto
     * Crea un registro en la tabla 'objetos' con los datos proporcionados.
     * Establece fecha_publicacion a NOW() y marca eliminado = 0 por defecto.
     *
     * /Recibe: string $nombre, $marca, $estado, $descripcion, $categoria, $nombreImagen, int $id_alumno
     * /Devuelve: array ['exito' => true] o ['exito' => false, 'error' => mensaje]
     */
    public function crearProducto($nombre, $marca, $estado, $descripcion, $categoria, $nombreImagen, $id_alumno) {
        $sql = "INSERT INTO objetos (nombre, marca, estado, descripcion, categoria, imagen, ID, fecha_publicacion) 
                VALUES (:nombre, :marca, :estado, :descripcion, :categoria, :imagen, :id_alumno, NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':imagen', $nombreImagen);
            $stmt->bindParam(':id_alumno', $id_alumno);
            
            if ($stmt->execute()) {
                return ['exito' => true];
            } else {
                return ['exito' => false, 'error' => 'No se pudo guardar en la BD.'];
            }
        } catch (PDOException $e) {
            return ['exito' => false, 'error' => $e->getMessage()];
        }
    }

   /**
     * SOFT DELETE: Marca un producto como eliminado (no lo borra físicamente)
     * Establece eliminado = 1, registra la fecha y el usuario que eliminó.
     * El producto sigue existiendo en la BD pero no aparecerá en listados normales.
     *
     * /Recibe: int $id_producto, int $id_usuario
     * /Devuelve: bool
     */
    public function eliminarProducto($id_producto, $id_usuario) {
        try {
            $sql = "UPDATE objetos 
                    SET eliminado = 1, 
                        fecha_eliminacion = NOW(), 
                        eliminado_por = :usuario 
                    WHERE id_objetos = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_producto, PDO::PARAM_INT);
            $stmt->bindParam(':usuario', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un producto completo
     * Actualiza nombre, marca, estado, descripción, categoría e imagen (opcional).
     * Solo actualiza productos NO eliminados (eliminado = 0).
     *
     * /Recibe: int $id_producto, string $nombre, $marca, $estado, $descripcion, $categoria, string|null $imagen
     * /Devuelve: bool
     */
    public function actualizarProducto($id_producto, $nombre, $marca, $estado, $descripcion, $categoria, $imagen = null) {
        try {
            if ($imagen) {
                $sql = "UPDATE objetos SET 
                        nombre = :nombre,
                        marca = :marca,
                        estado = :estado,
                        descripcion = :desc,
                        categoria = :categoria,
                        imagen = :img 
                        WHERE id_objetos = :id AND eliminado = 0";
            } else {
                $sql = "UPDATE objetos SET 
                        nombre = :nombre,
                        marca = :marca,
                        estado = :estado,
                        descripcion = :desc,
                        categoria = :categoria 
                        WHERE id_objetos = :id AND eliminado = 0";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':desc', $descripcion);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':id', $id_producto);
            
            if ($imagen) {
                $stmt->bindParam(':img', $imagen);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar producto: " . $e->getMessage());
            return false;
        }
    }

   /**
     * Obtiene todos los productos eliminados (para administradores/coordinadores)
     * Muestra información del alumno propietario y quién eliminó el producto.
     *
     * /Recibe: Nada.
     * /Devuelve: array de productos eliminados o [] en error
     */
    public function obtenerProductosEliminados() {
        try {
            $sql = "SELECT p.*, 
                    a.nombre AS nombre_alumno,
                    ae.nombre AS eliminado_por_nombre
                    FROM objetos p 
                    JOIN alumnos a ON p.ID = a.ID 
                    LEFT JOIN alumnos ae ON p.eliminado_por = ae.ID
                    WHERE p.eliminado = 1
                    ORDER BY p.fecha_eliminacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener productos eliminados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Restaura un producto eliminado (recuperar soft delete)
     * Marca el producto como NO eliminado (eliminado = 0) y limpia datos de auditoría.
     *
     * /Recibe: int $id_producto
     * /Devuelve: bool
     */
    public function restaurarProducto($id_producto) {
        try {
            $sql = "UPDATE objetos 
                    SET eliminado = 0, 
                        fecha_eliminacion = NULL, 
                        eliminado_por = NULL 
                    WHERE id_objetos = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_producto, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al restaurar producto: " . $e->getMessage());
            return false;
        }
    }

    
    /**
     * Obtiene todas las categorías únicas con contador de productos
     * Solo cuenta productos NO eliminados (eliminado = 0).
     * Ordena alfabéticamente.
     *
     * /Recibe: Nada.
     * /Devuelve: array de categorías con contadores o [] en error
     */
    public function obtenerCategoriasUnicas() {
        try {
            $sql = "SELECT categoria, COUNT(*) as total 
                    FROM objetos 
                    WHERE categoria IS NOT NULL AND categoria != '' AND eliminado = 0
                    GROUP BY categoria
                    ORDER BY categoria ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene productos por categoría específica
     * Solo muestra productos NO eliminados (eliminado = 0).
     * Incluye información del alumno propietario.
     *
     * /Recibe: string $categoria
     * /Devuelve: array de productos o [] en error
     */
    public function obtenerPorCategoria($categoria) {
        try {
            $sql = "SELECT p.*, a.nombre AS nombre_alumno, a.carrera, a.turno
                    FROM objetos p
                    LEFT JOIN alumnos a ON p.ID = a.ID
                    WHERE p.categoria = :categoria AND p.eliminado = 0
                    ORDER BY p.fecha_publicacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

   

    /**
     * Agrega un comentario a un producto
     * Inserta un nuevo comentario con fecha actual.
     *
     * /Recibe: int $id_objeto, int $id_alumno, string $comentario
     * /Devuelve: bool
     */
    public function agregarComentario($id_objeto, $id_alumno, $comentario) {
        try {
            $sql = "INSERT INTO comentarios (id_objeto, id_alumno, comentario, fecha_comentario) 
                    VALUES (:id_obj, :id_alu, :com, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_obj', $id_objeto);
            $stmt->bindParam(':id_alu', $id_alumno);
            $stmt->bindParam(':com', $comentario);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al agregar comentario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los comentarios de un producto específico
     * Incluye información del alumno que comentó (nombre, carrera, turno).
     * Ordena por fecha de comentario ascendente (más antiguos primero).
     *
     * /Recibe: int $id_objeto
     * /Devuelve: array de comentarios o [] en error
     */
    public function obtenerComentariosPorObjeto($id_objeto) {
        try {
            $sql = "SELECT c.*, a.nombre AS nombre_alumno, a.carrera, a.turno
                    FROM comentarios c
                    JOIN alumnos a ON c.id_alumno = a.ID
                    WHERE c.id_objeto = :id_obj
                    ORDER BY c.fecha_comentario ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_obj', $id_objeto);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

   /**
     * Elimina un comentario por su ID
     * Borrado físico del comentario (no es soft delete).
     *
     * /Recibe: int $id_comentario
     * /Devuelve: bool
     */
    public function eliminarComentario($id_comentario) {
        try {
            $sql = "DELETE FROM comentarios WHERE id_comentario = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_comentario);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

   /**
     * Actualiza el texto de un comentario existente
     *
     * /Recibe: int $id_comentario, string $texto
     * /Devuelve: bool
     */
    public function actualizarComentario($id_comentario, $texto) {
        try {
            $sql = "UPDATE comentarios SET comentario = :texto WHERE id_comentario = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':texto', $texto);
            $stmt->bindParam(':id', $id_comentario);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

     /**
     * Obtiene un comentario específico por su ID
     *
     * /Recibe: int $id_comentario
     * /Devuelve: array|false
     */
    public function obtenerComentarioPorId($id_comentario) {
        try {
            $sql = "SELECT * FROM comentarios WHERE id_comentario = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_comentario);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verifica si un comentario pertenece a un alumno (Seguridad)
     *
     * Previene que un alumno edite/elimine comentarios de otros usuarios.
     *
     * /Recibe: int $id_comentario, int $id_alumno
     * /Devuelve: bool (true si el comentario le pertenece)
     */
    public function verificarPropietarioComentario($id_comentario, $id_alumno) {
        try {
            $sql = "SELECT id_comentario FROM comentarios WHERE id_comentario = :id_com AND id_alumno = :id_alu";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_com', $id_comentario);
            $stmt->bindParam(':id_alu', $id_alumno);
            $stmt->execute();
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>