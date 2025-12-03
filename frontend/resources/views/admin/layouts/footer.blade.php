<footer class="footer">
    <div class="container-fluid d-flex justify-content-between">
        <nav class="pull-left">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('accueil') }}" target="_blank">
                        KAZARIA Marketplace
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.help') }}">Aide</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.documentation') }}">Documentation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contact') }}" target="_blank">Support</a>
                </li>
            </ul>
        </nav>
        <div class="copyright">
            {{ date('Y') }}, fait avec <i class="fa fa-heart heart text-danger"></i> par
            <a href="{{ route('accueil') }}" target="_blank">KAZARIA</a>
        </div>
        <div>
            Version {{ config('app.version', '1.0.0') }} |
            <a href="{{ route('admin.changelog') }}">Changelog</a>
        </div>
    </div>
</footer>

