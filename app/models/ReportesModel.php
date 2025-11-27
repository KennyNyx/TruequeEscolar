<?php
class ReportesModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Reporte 1: Objetos por Categoría
     * Devuelve por cada categoría: total, activos y eliminados.
     *
     * /Recibe: Nada.
     * /Devuelve: array de filas: ['categoria','total','activos','eliminados']
     */
    public function obtenerObjetosPorCategoria() {
        try {
            $sql = "SELECT 
                        categoria,
                        COUNT(*) as total,
                        SUM(CASE WHEN eliminado = 0 THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN eliminado = 1 THEN 1 ELSE 0 END) as eliminados
                    FROM objetos 
                    GROUP BY categoria 
                    ORDER BY total DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerObjetosPorCategoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Reporte 2: Lugares de Reunión
     *  Por cada lugar: total de reuniones y desglose por estado.
     *
     * /Recibe: Nada.
     * /Devuelve: array de filas: ['lugar','total_reuniones','confirmadas','canceladas','pendientes','completadas']
     */
    public function obtenerLugaresReunion() {
        try {
            $sql = "SELECT 
                        lugar,
                        COUNT(*) as total_reuniones,
                        SUM(CASE WHEN estado_general = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
                        SUM(CASE WHEN estado_general = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
                        SUM(CASE WHEN estado_general = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado_general = 'completada' THEN 1 ELSE 0 END) as completadas
                    FROM reuniones 
                    WHERE eliminado = 0
                    GROUP BY lugar 
                    ORDER BY total_reuniones DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerLugaresReunion: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Reporte 3: Estado de Reuniones
     * Agrupa reuniones por estado y calcula porcentaje respecto del total de reuniones no eliminadas.
     *
     * /Recibe: Nada.
     * /Devuelve: array de filas: ['estado_general','total','porcentaje']
     */
    public function obtenerEstadoReuniones() {
        try {
            $sql = "SELECT 
                        estado_general,
                        COUNT(*) as total,
                        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reuniones WHERE eliminado = 0)), 2) as porcentaje
                    FROM reuniones 
                    WHERE eliminado = 0
                    GROUP BY estado_general 
                    ORDER BY total DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadoReuniones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener totales generales para estadísticas
     * 
     * /Recibe: Nada.
     * /Devuelve: array asociativo con keys: total_objetos, total_reuniones, total_alumnos, total_lugares
     */
    public function obtenerTotalesGenerales() {
        try {
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM objetos WHERE eliminado = 0) as total_objetos,
                        (SELECT COUNT(*) FROM reuniones WHERE eliminado = 0) as total_reuniones,
                        (SELECT COUNT(*) FROM alumnos) as total_alumnos,
                        (SELECT COUNT(DISTINCT lugar) FROM reuniones WHERE eliminado = 0) as total_lugares";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTotalesGenerales: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener detalles de objetos para tabla
     *
     * /Recibe: int $limit (cantidad máxima de filas)
     * /Devuelve: array de objetos con datos agregados (comentarios)
     */
    public function obtenerDetalleObjetos($limit = 50) {
        try {
            $sql = "SELECT 
                        o.id_objetos,
                        o.nombre,
                        o.categoria,
                        o.estado,
                        o.fecha_publicacion,
                        a.nombre as nombre_alumno,
                        (SELECT COUNT(*) FROM comentarios WHERE id_objeto = o.id_objetos) as comentarios
                    FROM objetos o
                    LEFT JOIN alumnos a ON o.ID = a.ID
                    WHERE o.eliminado = 0
                    ORDER BY o.fecha_publicacion DESC
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerDetalleObjetos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener detalles de reuniones para tabla
     * 
     * /Recibe: int $limit (cantidad máxima de filas)
     * /Devuelve: array de reuniones con nombres de participantes y objetos
     */
    public function obtenerDetalleReuniones($limit = 50) {
        try {
            $sql = "SELECT 
                        r.id_reunion,
                        r.lugar,
                        r.fecha_reunion,
                        r.hora_reunion,
                        r.estado_general,
                        a1.nombre as creador,
                        a2.nombre as participante,
                        r.objeto_creador,
                        r.objeto_participante
                    FROM reuniones r
                    LEFT JOIN alumnos a1 ON r.id_alumno_creador = a1.ID
                    LEFT JOIN alumnos a2 ON r.id_alumno_participante = a2.ID
                    WHERE r.eliminado = 0
                    ORDER BY r.fecha_reunion DESC
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerDetalleReuniones: " . $e->getMessage());
            return [];
        }
    }
}
?>