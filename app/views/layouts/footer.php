        </section>
    </main>

    <!-- Modal de Confirmación (6.2) -->
    <div id="modal-confirmar-overlay" class="modal-overlay" style="display:none;">
        <div class="modal" style="max-width:400px;">
            <div class="modal-header">
                <h3 class="modal-titulo">Confirmar Operación</h3>
                <button type="button" class="modal-cerrar" onclick="document.getElementById('modal-confirmar-overlay').classList.remove('activo');">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modal-confirmar-msg">¿Desea guardar los cambios y finalizar la operación?</p>
                <input type="hidden" id="modal-confirmar-form" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-confirmar-overlay').classList.remove('activo');">Cancelar</button>
                <button type="button" class="btn btn-primario" onclick="ejecutarConfirmacion()">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => { 
            // Inicializar Lucide
            if (typeof lucide !== 'undefined') lucide.createIcons(); 
            
            // Inicialización Global de Tom Select
            inicializarTomSelect();
        });

        // Función para inicializar Tom Select en todos los selects
        function inicializarTomSelect(contenedor = document) {
            if (typeof TomSelect === 'undefined') return;

            const selects = contenedor.querySelectorAll('select:not(.no-ts):not(.tomselected)');
            selects.forEach(select => {
                const ajaxUrl = select.getAttribute('data-ajax-url');
                
                // Configuración base
                const config = {
                    plugins: ['dropdown_input'],
                    allowEmptyOption: true,
                    maxOptions: 50,
                    placeholder: select.getAttribute('placeholder') || 'Seleccione una opción...',
                    onInitialize: function() {
                        this.control.classList.add('fade-in');
                    }
                };

                // Configuración AJAX si existe URL
                if (ajaxUrl) {
                    config.valueField = 'id';
                    config.labelField = 'text';
                    config.searchField = 'text';
                    config.load = function(query, callback) {
                        if (!query.length) return callback();
                        fetch(`${ajaxUrl}&q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(json => {
                                // Mapear resultados al formato de Tom Select
                                const results = json.map(item => {
                                    let text = '';
                                    if (item.nombre_generico) {
                                        text = `${item.nombre_generico} ${item.concentracion || ''}`;
                                    } else if (item.razon_social) {
                                        text = `${item.razon_social} (${item.rif || ''})`;
                                    } else if (item.nombre) {
                                        text = `${item.nombre} ${item.apellido || ''}`;
                                    } else {
                                        text = item.cedula || item.rif || 'Sin nombre';
                                    }

                                    return {
                                        id: item.id,
                                        text: text,
                                        grupo_codigo: item.grupo_codigo || '',
                                        tipo_medicamento: item.tipo_medicamento || ''
                                    };
                                });
                                callback(results);
                            }).catch(() => {
                                callback();
                            });
                    };

                    // Transferir metadatos al <option> real para compatibilidad con scripts existentes
                    config.onItemAdd = function(value, item) {
                        const data = this.options[value];
                        const option = select.querySelector(`option[value="${value}"]`);
                        if (option && data) {
                            if (data.grupo_codigo) option.setAttribute('data-grupo', data.grupo_codigo);
                            if (data.tipo_medicamento) option.setAttribute('data-tipo', data.tipo_medicamento);
                        }
                    };
                }

                try {
                    new TomSelect(select, config);
                } catch (e) {
                    console.warn('Error inicializando Tom Select en:', select, e);
                }
            });
        }

        // Observador para elementos dinámicos (Nativo JS)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // ELEMENT_NODE
                        if (node.tagName === 'SELECT') inicializarTomSelect(node.parentNode);
                        else if (node.querySelectorAll) {
                            if (node.querySelectorAll('select').length > 0) inicializarTomSelect(node);
                        }
                    }
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });

        document.querySelectorAll('.modal-overlay').forEach(o => {
            o.addEventListener('click', e => { if (e.target === o) o.classList.remove('activo'); });
        });
    </script>
</body>
</html>
