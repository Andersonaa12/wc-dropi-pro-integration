<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap ">
    <?php
        if (isset($_GET['message'])) {
            if ($_GET['message'] == 'success') {
                echo '<div class="notice notice-success is-dismissible">
                        <p>' . esc_html__('Los datos se han guardado correctamente.', 'wc-dropi-pro-integration') . '</p>
                      </div>';
             
            } elseif ($_GET['message'] == 'success-api') {
                echo '<div class="notice notice-success is-dismissible">
                        <p>' . esc_html__('Autenticación Correcta Dropi - Los Datos se han guardado correctamente.', 'wc-dropi-pro-integration') . '</p>
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
                <div class="form-group mb-3">
                    <label for="store_email" class="fs-6"><?php echo esc_html__('Correo Electrónico de la Tienda', 'wc-dropi-pro-integration'); ?></label>
                    <input type="email" name="store_email" id="store_email" class="form-control" placeholder="<?php esc_attr_e('Ingresa tu usuario', 'wc-dropi-pro-integration'); ?>" required />
                </div>
                <div class="form-group mb-3">
                    <label for="store_token" class="fs-6"><?php echo esc_html__('Token de la Tienda', 'wc-dropi-pro-integration'); ?></label>
                    <div class="input-group">
                        <input type="password" name="store_token" id="store_token" class="form-control" placeholder="<?php esc_attr_e('Ingresa tu token', 'wc-dropi-pro-integration'); ?>" required/>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <input type="checkbox" id="connect_other_platform" name="connect_other_platform" value="1" />
                    <label for="connect_other_platform" class="fs-6"><?php echo esc_html__('Conectar con otra plataforma', 'wc-dropi-pro-integration'); ?></label>
                </div>

                <div class="form-group mb-3" id="platform_code_group" style="display: none;">
                    <label for="platform_code" class="fs-6"><?php echo esc_html__('Código de plataforma ', 'wc-dropi-pro-integration'); ?></label>
                    <input type="text" name="platform_code" id="platform_code" class="form-control" placeholder="<?php esc_attr_e('Ingresa tu código de plataforma', 'wc-dropi-pro-integration'); ?>" />
                </div>

                <div>
                    <p><?php echo esc_html__('Para obtener tu token de WooCommerce, ve a tus tiendas desde tu panel administrativo (pinchando en tu icono de tienda, en la parte lateral del menu administrativo). En la sección "Tiendas", ingresa a editar una tienda creada de WooCommerce. podrás copiarlo y pegarlo aquí.', 'wc-dropi-pro-integration'); ?></p>
                </div>

                <div>
                    <?php submit_button(esc_html__('Iniciar Sesión', 'wc-dropi-pro-integration'), '', '', false, array('class' => 'btn btn-dropi')); ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('connect_other_platform').addEventListener('change', function() {
    var platformCodeGroup = document.getElementById('platform_code_group');
    if(this.checked) {
        platformCodeGroup.style.display = 'block';
    } else {
        platformCodeGroup.style.display = 'none';
    }
});
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
