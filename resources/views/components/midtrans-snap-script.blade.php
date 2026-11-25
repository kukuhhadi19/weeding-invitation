@if (filled(config('midtrans.client_key')))
    <script
        src="{{ config('midtrans.snap_script_url') }}"
        data-client-key="{{ config('midtrans.client_key') }}"
        data-navigate-once
    ></script>
@endif

<script data-navigate-once>
    if (!window.__midtransSnapListenerAttached) {
        window.__midtransSnapListenerAttached = true;

        window.addEventListener('open-midtrans-snap', function (event) {
            const rawDetail = event.detail || {};
            const detail = Array.isArray(rawDetail) ? (rawDetail[0] || {}) : rawDetail;
            const snapToken = detail.snapToken;
            const orderId = detail.orderId || '';
            const transactionsUrl = @json(route('transactions.index'));

            if (!snapToken || typeof window.snap === 'undefined') {
                window.dispatchEvent(new CustomEvent('snap-unavailable', {
                    detail: {
                        message: 'Pop-up Midtrans Snap belum siap atau client key Sandbox belum dikonfigurasi.',
                    },
                }));

                return;
            }

            const redirectWithStatus = function (result) {
                const url = new URL(transactionsUrl, window.location.origin);

                if (orderId) {
                    url.searchParams.set('order', orderId);
                }

                url.searchParams.set('snap_result', result);
                window.location.href = url.toString();
            };

            window.snap.pay(snapToken, {
                onSuccess: function () {
                    redirectWithStatus('success');
                },
                onPending: function () {
                    redirectWithStatus('pending');
                },
                onError: function () {
                    redirectWithStatus('error');
                },
                onClose: function () {
                    redirectWithStatus('closed');
                },
            });
        });
    }
</script>
