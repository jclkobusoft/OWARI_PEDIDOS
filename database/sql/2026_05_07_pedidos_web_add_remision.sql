-- =============================================================================
-- Agregar pedido_sae_remision a pedidos_web
-- =============================================================================
-- Contexto:
--   El carrito v2 puede generar DOS pedidos SAE para un mismo PedidoWeb cuando
--   el cliente tiene W en pos.4 de CLASIFIC (split E01 factura + E03 remision).
--   La columna existente `pedido_sae` ya contiene el folio principal y se usa
--   en producción. Para no romper código existente, conservamos su semantica
--   (= folio de factura) y AGREGAMOS una nueva columna para el de remision.
--
--   - pedido_sae          → folio E01 (factura). EXISTENTE, sin cambios.
--   - pedido_sae_remision → folio E03 (remision). NUEVO, opcional.
--
--   Si el cliente NO tiene W:
--     pedido_sae          = folio único generado
--     pedido_sae_remision = NULL
--
--   Si el cliente tiene W y se generaron ambos:
--     pedido_sae          = folio empresa 1
--     pedido_sae_remision = folio empresa 3
--
--   Si solo se generó uno (por estado de la cola), el otro queda NULL hasta
--   que el comando artisan procesar-sae-pendientes lo logre.
-- =============================================================================

ALTER TABLE pedidos_web
    ADD COLUMN pedido_sae_remision VARCHAR(50) NULL;

-- Opcional: registrar la "migracion" en la tabla migrations para que Laravel
-- no intente recrearla. (Solo si tu equipo usa el sistema de migrations.)
-- INSERT INTO migrations (migration, batch)
-- VALUES (
--     '2026_05_07_120000_add_pedido_sae_remision_to_pedidos_web',
--     (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations)
-- );
