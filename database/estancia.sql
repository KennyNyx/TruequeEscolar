-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-11-2025 a las 18:14:47
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `estancia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_admin` int(11) NOT NULL,
  `nombre` varchar(250) DEFAULT NULL,
  `correo` varchar(250) DEFAULT NULL,
  `contra` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_admin`, `nombre`, `correo`, `contra`) VALUES
(5, 'Santiago Admin', 'santiAdmin@upemor.edu.mx', '$2y$10$WkdijmQ0jRG7xWyOYN.hHe3DPlXalN0dKMeBrFfHfZ7Ob.1TyY9Yy'),
(8, 'Antony Admin', 'AntonyAdmin@upemor.edu.mx', '$2y$10$zPzQk1LLFaRh645lOo9ZCuuvchPWxGKRQUPRyIoLdTOLngbhPWCSi');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `ID` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `carrera` varchar(150) DEFAULT NULL,
  `cuatrimestre` int(11) DEFAULT NULL,
  `turno` varchar(100) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `contra` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`ID`, `nombre`, `carrera`, `cuatrimestre`, `turno`, `correo`, `contra`) VALUES
(11, 'Antony Jonathan Garcia Ocampo', 'ITI', 7, 'vespertino', 'goao230888@upemor.edu.mx', '$2y$10$NlwCbG/S4A/aizSE1d7EI.4mfG3/0AHqrxGoDJB6ypl2NZxTnQumS'),
(21, 'Santiago Millan Martinez', 'ITI', 7, 'vespertino', 'mmso2331322@upemor.edu.mx', '$2y$10$cB6FK.YGsfA12IAZqcq4KOnu4UFOhldNmkG7ZIjJ7GKzlDyG96V9G'),
(24, 'Alan Gonzales Villa', 'ITI', 1, 'vespertino', 'gvao23777@upemor.edu.mx', '$2y$10$vP1ts5E6yWz8vXRChNfAT.G5qcdzThnyFrtT1SYiUhLvNcBHpXoee'),
(40, 'Cristopher Emiliano Millan Martinez', 'LAE', 9, 'matutino', 'mmceo231313@upemor.edu.mx', '$2y$10$C0IkikzTePjbzuZVXRldJOKvcwPJ4tQbeKdd2AEgmhSW42bxhi.Yy'),
(42, 'Ana Gabriela Martinez Camargo', 'LAE', 9, 'matutino', 'mcago221402@upemor.edu.mx', '$2y$10$nnW/GQV9E4jtHCyeJwTU/.a4Md9ykLw3WqcBNvrilyXFnA4mxMzt2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat`
--

CREATE TABLE `chat` (
  `id_chat` int(11) NOT NULL,
  `id_emisor` int(11) DEFAULT NULL,
  `id_receptor` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) DEFAULT NULL,
  `editado` tinyint(1) DEFAULT 0,
  `imagenes` text DEFAULT NULL,
  `fecha_edicion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `chat`
--

INSERT INTO `chat` (`id_chat`, `id_emisor`, `id_receptor`, `mensaje`, `fecha_hora`, `estado`, `editado`, `imagenes`, `fecha_edicion`) VALUES
(29, 11, 21, 'hola', '2025-11-24 17:36:04', 0, 0, NULL, NULL),
(32, 21, 11, 'hola antony, como estas?', '2025-11-24 18:12:07', 0, 0, NULL, NULL),
(33, 21, 11, 'mira, te interesa este producto?', '2025-11-24 18:13:00', 0, 0, '[\"uploads\\/chat\\/6924a02cc97ba_1764007980.jpg\"]', NULL),
(34, 21, 42, 'Hola gaby, estoy interesado en tu calculadora científica', '2025-11-24 20:42:43', 0, 0, NULL, NULL),
(35, 21, 42, 'yo puedo ofrecerte el libro de contaduría', '2025-11-24 20:44:38', 0, 0, NULL, NULL),
(36, 42, 21, 'okey, me puedes mandar foto del producto?', '2025-11-24 20:46:11', 0, 0, NULL, NULL),
(37, 21, 42, 'si, te mando foto cuando pueda', '2025-11-24 20:51:13', 1, 0, NULL, NULL),
(38, 21, 11, 'si vas a querer? el producto, ya no me contestaste?', '2025-11-25 16:14:56', 0, 0, NULL, NULL),
(39, 11, 21, 'si pa, profa', '2025-11-25 16:15:22', 1, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_sesiones`
--

CREATE TABLE `chat_sesiones` (
  `id_sesion` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `ultima_actividad` timestamp NOT NULL DEFAULT current_timestamp(),
  `esta_escribiendo` enum('no','si') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `chat_sesiones`
--

INSERT INTO `chat_sesiones` (`id_sesion`, `id_usuario`, `ultima_actividad`, `esta_escribiendo`) VALUES
(1, 21, '2025-11-25 16:15:02', 'no'),
(2, 24, '2025-11-23 21:43:33', 'no'),
(3, 11, '2025-11-25 16:19:26', 'no'),
(4, 42, '2025-11-24 20:47:24', 'si');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `id_objeto` int(11) DEFAULT NULL,
  `id_alumno` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `fecha_comentario` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id_comentario`, `id_objeto`, `id_alumno`, `comentario`, `fecha_comentario`) VALUES
(15, 16, 40, 'Hola, a mi me interesa, te mando mensaje por privado ', '2025-11-23 00:45:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coordinadores`
--

CREATE TABLE `coordinadores` (
  `id_coordi` int(11) NOT NULL,
  `nombre` varchar(250) DEFAULT NULL,
  `correo` varchar(250) DEFAULT NULL,
  `contra` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `coordinadores`
--

INSERT INTO `coordinadores` (`id_coordi`, `nombre`, `correo`, `contra`) VALUES
(2, 'Santiago prueba1', 'santiCoordi@upemor.edu.mx', '$2y$10$Q81Oni6aGj33KaLZhfqEf.rIeVplAeCxy7xmUTL7H/.MfZE2gqhjK'),
(5, 'Antony Jonathan Garcia Ocampo', 'AntonyCoordi@upemor.edu.mx', '$2y$10$Qe3czGxGnSSYXnTduyoU4.oCm65O9RNhp.VSw.HvsBEfQqVEEt0g2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetos`
--

CREATE TABLE `objetos` (
  `id_objetos` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `ID` int(11) NOT NULL,
  `fecha_publicacion` datetime DEFAULT current_timestamp(),
  `eliminado` tinyint(1) DEFAULT 0 COMMENT '0=Activo, 1=Eliminado',
  `fecha_eliminacion` datetime DEFAULT NULL COMMENT 'Fecha en que se eliminó',
  `eliminado_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que eliminó'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `objetos`
--

INSERT INTO `objetos` (`id_objetos`, `nombre`, `marca`, `estado`, `descripcion`, `categoria`, `imagen`, `ID`, `fecha_publicacion`, `eliminado`, `fecha_eliminacion`, `eliminado_por`) VALUES
(16, 'Teclado mecánico', 'Machenike', 'usado_bueno', 'Teclado mecánico con switches rojos, con RGB, vine incluido con cable de entrada tipo c aparte de traer dos switches de repuesto con sus herramientas para quitarlos\r\nPido a cambio un Mouse Gamer, no importa la marca solo que este en  buenas condiciones', 'Electronica', 'b16f264d9e6e2fa678e82657add0c5a6.png', 21, '2025-11-22 23:50:36', 0, NULL, NULL),
(17, 'Libro de ingles Top Notch 4', '', 'nuevo', 'Ofrezco mi libro de ingles Top Notch 4 con código de plataforma\r\nPido algún electrónico a cambio', 'Libros', '840e7faf8fbac7fd6f094d779b1e7c6a.jpg', 11, '2025-11-22 23:55:29', 0, NULL, NULL),
(18, 'Mouse gamer', 'Yeyian', 'usado_detalles', 'Mouse Gamer con luces RGB, cuenta con un software para modificarlo a tu gusto, puedes cambiar el DPI hasta 6000, solo que no le sirve el botón para cambiar entre varios DPS, por eso el sotware\r\nPido a cambio unos audífonos con micrófono, para jugar mas que nada', 'Electronica', 'c2242926cdb97b7c0a6dc355fe6adad2.jpg', 24, '2025-11-23 00:41:42', 0, NULL, NULL),
(19, 'Bata de laboratorio', '', 'seminuevo', 'Doy mi bata de laboratorio, solo la ocupe 1 cuatrimestre\r\nPido a cambio unos audífonos bluetooth', 'Ropa', 'e543d21932a1bf08c2d84124077fc3f7.jpg', 40, '2025-11-23 00:45:16', 0, NULL, NULL),
(20, 'Calculadora cientifica', 'Casio', 'seminuevo', 'Calculadora científica Casio, tiene para hacer integrales y solo la use 1 cuatrimestre\r\nPido a cambio un libro de contaduría', 'Calculadoras', 'c43f256ee2c67d6a3ce7f2749deca57e.jpeg', 42, '2025-11-23 01:01:20', 0, NULL, NULL),
(21, 'Lonchera para comida', 'Artic zone', 'nuevo', 'Lonchera para tu comida, le puedes poner bolsas de hielo (ya vienen con la lonchera) para mantener bien tu comida\r\nEl trueque puede ser a tratar, ver que ofrecen', 'Otro', 'b7a9276dbc2ea80c7be32e674d218d6f.jpg', 21, '2025-11-23 01:09:47', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

CREATE TABLE `resenas` (
  `id_resena` int(11) NOT NULL,
  `id_reunion` int(11) DEFAULT NULL,
  `id_alumno_resena` int(11) DEFAULT NULL,
  `id_alumno_evaluado` int(11) DEFAULT NULL,
  `objeto_evaluado` varchar(255) DEFAULT NULL,
  `calificacion` int(11) DEFAULT NULL CHECK (`calificacion` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `imagenes` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `editado` tinyint(4) DEFAULT 0,
  `fecha_edicion` datetime DEFAULT NULL,
  `eliminado` tinyint(1) DEFAULT 0,
  `fecha_eliminacion` datetime DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resenas`
--

INSERT INTO `resenas` (`id_resena`, `id_reunion`, `id_alumno_resena`, `id_alumno_evaluado`, `objeto_evaluado`, `calificacion`, `comentario`, `imagenes`, `fecha_creacion`, `editado`, `fecha_edicion`, `eliminado`, `fecha_eliminacion`, `eliminado_por`) VALUES
(1, NULL, 21, 24, 'No me acuerdo', 1, 'buena experiencia, el compañero fue muy amable', '[\"0ac8e6c52785ff6a622d8e8344eb3edd.png\"]', '2025-11-22 17:36:00', 0, NULL, 1, '2025-11-22 23:42:13', 21),
(3, NULL, 21, 40, 'Mouse gamer', 5, 'muy bonito, sirve y esta perfecto\r\nigual el otro alumno muy bien', '[\"uploads\\/resenas\\/6921fc97d02c0_Apex Legends 06_11_2025 12_02_15 a. m..png\",\"69229dcea6b31_1763876302.png\"]', '2025-11-22 18:10:31', 1, '2025-11-22 23:38:22', 1, '2025-11-22 23:38:40', 21),
(5, NULL, 21, 42, 'Calculadora científica', 5, 'muy buen producto la neta', '[\"6922b55f22bea_1763882335.jpeg\"]', '2025-11-23 07:18:55', 1, '2025-11-23 01:31:33', 1, '2025-11-23 01:32:04', 21),
(6, NULL, 21, 24, 'Teclado mecánico', 5, 'prueba de reseña coordi', '[\"6922bcc0b9b2d_1763884224.png\"]', '2025-11-23 07:50:24', 0, NULL, 1, '2025-11-23 01:50:52', NULL),
(7, NULL, 21, 42, 'Calculadora científica', 5, 'Cumple bien con su función, me ha ayudado para calculo, sin ninguna queja', '[\"6924c588918cc_1764017544.jpeg\"]', '2025-11-24 20:52:24', 0, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reuniones`
--

CREATE TABLE `reuniones` (
  `id_reunion` int(11) NOT NULL,
  `id_alumno_creador` int(11) DEFAULT NULL,
  `id_alumno_participante` int(11) DEFAULT NULL,
  `correo_creador` varchar(255) DEFAULT NULL,
  `correo_participante` varchar(255) DEFAULT NULL,
  `objeto_creador` varchar(255) DEFAULT NULL,
  `objeto_participante` varchar(255) DEFAULT NULL,
  `lugar` varchar(100) DEFAULT NULL,
  `fecha_reunion` date DEFAULT NULL,
  `hora_reunion` time DEFAULT NULL,
  `estado_creador` enum('pendiente','confirmado','cancelado') DEFAULT 'pendiente',
  `estado_participante` enum('pendiente','confirmado','cancelado') DEFAULT 'pendiente',
  `estado_coordinador` enum('pendiente','confirmado','cancelado') DEFAULT 'pendiente',
  `estado_general` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `notas` text DEFAULT NULL,
  `eliminado` tinyint(1) DEFAULT 0 COMMENT '0=Activo, 1=Eliminado',
  `fecha_eliminacion` datetime DEFAULT NULL COMMENT 'Fecha en que se eliminó',
  `eliminado_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que eliminó'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reuniones`
--

INSERT INTO `reuniones` (`id_reunion`, `id_alumno_creador`, `id_alumno_participante`, `correo_creador`, `correo_participante`, `objeto_creador`, `objeto_participante`, `lugar`, `fecha_reunion`, `hora_reunion`, `estado_creador`, `estado_participante`, `estado_coordinador`, `estado_general`, `fecha_creacion`, `notas`, `eliminado`, `fecha_eliminacion`, `eliminado_por`) VALUES
(13, 24, 21, 'gvao23777@upemor.edu.mx', 'mmso2331322@upemor.edu.mx', 'Mouse Gamer', 'Teclado Mecánico', 'UD2', '2025-11-23', '08:00:00', 'confirmado', 'confirmado', 'confirmado', 'confirmada', '2025-11-23 06:53:53', 'Voy a estar arriba en el salón 103 a esa hora', 0, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id_chat`);

--
-- Indices de la tabla `chat_sesiones`
--
ALTER TABLE `chat_sesiones`
  ADD PRIMARY KEY (`id_sesion`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_objeto` (`id_objeto`),
  ADD KEY `id_alumno` (`id_alumno`);

--
-- Indices de la tabla `coordinadores`
--
ALTER TABLE `coordinadores`
  ADD PRIMARY KEY (`id_coordi`);

--
-- Indices de la tabla `objetos`
--
ALTER TABLE `objetos`
  ADD PRIMARY KEY (`id_objetos`),
  ADD KEY `id_alumno` (`ID`),
  ADD KEY `idx_eliminado` (`eliminado`);

--
-- Indices de la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id_resena`),
  ADD KEY `id_reunion` (`id_reunion`),
  ADD KEY `id_alumno_resena` (`id_alumno_resena`),
  ADD KEY `id_alumno_evaluado` (`id_alumno_evaluado`),
  ADD KEY `idx_eliminado` (`eliminado`);

--
-- Indices de la tabla `reuniones`
--
ALTER TABLE `reuniones`
  ADD PRIMARY KEY (`id_reunion`),
  ADD KEY `id_alumno_creador` (`id_alumno_creador`),
  ADD KEY `id_alumno_participante` (`id_alumno_participante`),
  ADD KEY `idx_eliminado` (`eliminado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `chat`
--
ALTER TABLE `chat`
  MODIFY `id_chat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `chat_sesiones`
--
ALTER TABLE `chat_sesiones`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `coordinadores`
--
ALTER TABLE `coordinadores`
  MODIFY `id_coordi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `objetos`
--
ALTER TABLE `objetos`
  MODIFY `id_objetos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `reuniones`
--
ALTER TABLE `reuniones`
  MODIFY `id_reunion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`id_objeto`) REFERENCES `objetos` (`id_objetos`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `objetos`
--
ALTER TABLE `objetos`
  ADD CONSTRAINT `objetos_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `alumnos` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`id_reunion`) REFERENCES `reuniones` (`id_reunion`) ON DELETE CASCADE,
  ADD CONSTRAINT `resenas_ibfk_2` FOREIGN KEY (`id_alumno_resena`) REFERENCES `alumnos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `resenas_ibfk_3` FOREIGN KEY (`id_alumno_evaluado`) REFERENCES `alumnos` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reuniones`
--
ALTER TABLE `reuniones`
  ADD CONSTRAINT `reuniones_ibfk_1` FOREIGN KEY (`id_alumno_creador`) REFERENCES `alumnos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reuniones_ibfk_2` FOREIGN KEY (`id_alumno_participante`) REFERENCES `alumnos` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
