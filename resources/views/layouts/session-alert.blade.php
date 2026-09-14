@php
    $sessionAlert = null;

    if (session('success')) {
        $sessionAlert = [
            'icon' => 'success',
            'title' => 'Operación completada',
            'message' => session('success'),
            'toast' => true,
        ];
    } elseif (session('error')) {
        $sessionAlert = [
            'icon' => 'error',
            'title' => 'No se pudo completar la operación',
            'message' => session('error'),
            'toast' => true,
        ];
    } elseif ($errors->any()) {
        $sessionAlert = [
            'icon' => 'warning',
            'title' => 'Revisa los datos del formulario',
            'message' => $errors->all(),
            'toast' => false,
        ];
    }
@endphp

<script>
    window.showAlert = function ({
        icon = 'info',
        title = '',
        message = '',
        toast = true,
    } = {}) {
        if (typeof Swal === 'undefined') {
            return;
        }

        const messages = Array.isArray(message) ? message : [message];

        Swal.fire({
            icon,
            title,
            text: messages.filter(Boolean).join('\n'),
            toast,
            position: toast ? 'top-end' : 'center',
            timer: toast ? 4500 : undefined,
            timerProgressBar: toast,
            showConfirmButton: !toast,
            showCloseButton: !toast,
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'text-white btn bg-custom-500 border-custom-500 hover:bg-custom-600 hover:border-custom-600',
            },
            buttonsStyling: false,
        });
    };

    @if ($sessionAlert)
        document.addEventListener('DOMContentLoaded', function () {
            window.showAlert(@json($sessionAlert));
        });
    @endif
</script>