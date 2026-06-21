/**
 * NEAMEE admin — close stuck modal overlays after Livewire updates (delete confirm).
 */
document.addEventListener('livewire:init', function () {
    if (typeof Livewire === 'undefined') {
        return;
    }

    Livewire.hook('commit', function (_ref) {
        var succeed = _ref.succeed;
        succeed(function () {
            requestAnimationFrame(function () {
                document.querySelectorAll('.fi-modal').forEach(function (modal) {
                    var win = modal.querySelector('.fi-modal-window');
                    var open = modal.style.display !== 'none' && !modal.hasAttribute('hidden');

                    if (!open) {
                        return;
                    }

                    if (win && win.offsetParent === null && win.getClientRects().length === 0) {
                        modal.style.display = 'none';
                    }
                });
            });
        });
    });
});
