<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <?php
    if (isset($_GET['message'])) {
        if ($_GET['message'] == 'success') {
            echo '<div class="notice notice-success is-dismissible">
                    <p>' . esc_html__('Los datos se han guardado correctamente.', 'wc-dropi-pro-integration') . '</p>
                </div>';
        } elseif ($_GET['message'] == 'invalid_credentials') {
            echo '<div class="notice notice-error is-dismissible">
                    <p>' . esc_html__('Credenciales inválidas. Por favor, verifica tu email y token.', 'wc-dropi-pro-integration') . '</p>
                </div>';
        } elseif ($_GET['message'] == 'error_connection') {
            echo '<div class="notice notice-error is-dismissible">
                    <p>' . esc_html__('Error al comunicarse con la API. Por favor, intenta nuevamente.', 'wc-dropi-pro-integration') . '</p>
                </div>';
        } elseif ($_GET['message'] == 'missing_fields') {
            echo '<div class="notice notice-error is-dismissible">
                    <p>' . esc_html__('Por favor, completa todos los campos requeridos.', 'wc-dropi-pro-integration') . '</p>
                </div>';
        } elseif (isset($_GET['message']) && $_GET['message'] == 'error') {
            echo '<div class="notice notice-danger is-dismissible">
                    <p>' . esc_html__('Ha ocurrido un error al guardar los datos.', 'wc-dropi-pro-integration') . '</p>
                  </div>';
        } elseif ($_GET['message'] == 'success-api') {
            echo '<div class="notice notice-success is-dismissible">
                    <p>' . esc_html__('Autenticación Exitosa Dropi - Los Datos se han guardado correctamente.', 'wc-dropi-pro-integration') . '</p>
                  </div>';
        }
    }
    if (isset($_GET['message'])) {
        if ($_GET['message'] == 'logout_success') {
            echo '<div class="notice notice-success is-dismissible">
                    <p>' . esc_html__('Has cerrado sesión correctamente y tus datos han sido eliminados.', 'wc-dropi-pro-integration') . '</p>
                </div>';
        }
    }
    ?>
    <div class="card mx-auto">
        <?php
            $code_soft_platform = !empty($data[0]->code_soft_platform) ? $data[0]->code_soft_platform : 'DROPIPRO';
            $logo_url = esc_url(plugin_dir_url(__FILE__) . '../../assets/images/logos/' . $code_soft_platform . '.png');
        ?>
        <div class="text-center p-2 <?php echo esc_attr(strtolower($code_soft_platform) . '-logo'); ?>">
            <img src="<?php echo $logo_url; ?>" alt="<?php esc_attr_e('Logo', 'wc-dropi-pro-integration'); ?>" style="max-height:3rem;">
        </div>

        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo esc_attr($data[0]->id); ?>">
                <input type="hidden" name="store_token" value="<?php echo esc_attr($data[0]->token); ?>" class="form-control"/>
                <input type="hidden" name="store_email" id="store_email" value="<?php echo esc_attr($data[0]->email); ?>" class="form-control"/>
                <input type="hidden" name="store" id="store" value="<?php echo esc_attr($data[0]->store); ?>" class="form-control" />
                <input type="hidden" id="sync_orders" name="sync_orders" value="1" <?php checked(1, $data[0]->sync_orders, true); ?> />
                <input type="hidden" id="sync_stock" name="sync_stock" value="1" <?php checked(1, $data[0]->sync_stock, true); ?> />

                <div class="form-group mb-3">
                    <label for="store_email" class="fs-6"><?php echo esc_html__('Usuario (Correo Electrónico)', 'wc-dropi-pro-integration'); ?></label>
                    <input type="email" name="store_email" id="store_email" value="<?php echo esc_attr($data[0]->email); ?>" class="form-control" disabled/>
                </div>

                <div class="form-group mb-3">
                    <label for="store_token" class="fs-6">
                        <?php 
                        echo esc_html__('Token de la Tienda ', 'wc-dropi-pro-integration') . 
                        esc_html(!empty($data[0]->code_soft_platform) ? $data[0]->code_soft_platform : 'Dropi Pro'); 
                        ?>
                    </label>
                    <div class="input-group">
                        <input type="password" name="store_token" id="store_token" value="<?php echo esc_attr($data[0]->token); ?>" class="form-control" disabled/>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa-solid fa-eye"></i> 
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="store" class="fs-6"><?php echo esc_html__('Nombre de la Tienda', 'wc-dropi-pro-integration'); ?></label>
                    <input type="text" name="store" id="store" value="<?php echo esc_attr($data[0]->store); ?>" class="form-control" disabled/>
                </div>
                
            </form>
            <form method="post" action="" id="logout-form">
                <input type="hidden" name="logout" value="1">
                <button type="button" class="btn btn-danger" id="logout-btn">
                    <?php echo esc_html__('Cerrar sesión', 'wc-dropi-pro-integration'); ?>
                </button>
            </form>

        </div>
    </div>
</div>
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    var passwordField = document.getElementById('store_token');
    var passwordFieldType = passwordField.getAttribute('type');
    if (passwordFieldType === 'password') {
        passwordField.setAttribute('type', 'text');
        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordField.setAttribute('type', 'password');
        this.innerHTML = '<i class="fas fa-eye"></i>';
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var logoutBtn = document.getElementById('logout-btn');
    var logoutForm = document.getElementById('logout-form');
    
    if (logoutBtn && logoutForm) {
        logoutBtn.addEventListener('click', function(e) {
            Swal.fire({
                title: '<?php echo esc_html__('¿Estás seguro?', 'wc-dropi-pro-integration'); ?>',
                text: '<?php echo esc_html__('Se eliminarán todos los datos asociados a tu cuenta en Dropi.', 'wc-dropi-pro-integration'); ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<?php echo esc_html__('Sí, cerrar sesión', 'wc-dropi-pro-integration'); ?>',
                cancelButtonText: '<?php echo esc_html__('Cancelar', 'wc-dropi-pro-integration'); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    logoutForm.submit();
                }
            });
        });
    } else {
        console.error('No se encontró el botón o el formulario de logout.');
    }
});

</script>