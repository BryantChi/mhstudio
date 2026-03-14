<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <span class="text-muted">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted">
                    Powered by Laravel {{ app()->version() }}
                </span>
            </div>
        </div>
    </div>
</footer>
