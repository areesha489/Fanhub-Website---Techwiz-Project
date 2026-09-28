<footer class="fh-footer">
    <div class="fh-footer-grid">
        <div class="fh-footer-brand">
            <a class="fh-brand" href="{{ route('home') }}">
                <span class="fh-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" width="16" height="16" aria-hidden="true">
                        <path fill="currentColor" d="M6 6h8.2c4.6 0 7.6 2.5 7.6 6.3 0 2.5-1.3 4.4-3.5 5.4L24.8 26h-5.2l-5.4-7.6H11V26H6V6zm5 4.1v5.2h2.7c2 0 3.2-1 3.2-2.6s-1.2-2.6-3.2-2.6H11z"/>
                    </svg>
                </span>
                <span class="fh-brand-copy">
                    <span class="fh-brand-text">Fan Hub<span class="fh-brand-plus">+</span></span>
                    <span class="fh-brand-tag">Explore. Discover. Connect.</span>
                </span>
            </a>
            <p>Your ultimate destination for all fandoms. Explore, discover and connect with a community that shares your passion.</p>
            <div class="fh-social">
                <a href="https://facebook.com" aria-label="Fan Hub+ on Facebook" rel="noopener noreferrer" target="_blank"><i class="bi bi-facebook"></i></a>
                <a href="https://x.com" aria-label="Fan Hub+ on X" rel="noopener noreferrer" target="_blank"><i class="bi bi-twitter-x"></i></a>
                <a href="https://instagram.com" aria-label="Fan Hub+ on Instagram" rel="noopener noreferrer" target="_blank"><i class="bi bi-instagram"></i></a>
                <a href="https://youtube.com" aria-label="Fan Hub+ on YouTube" rel="noopener noreferrer" target="_blank"><i class="bi bi-youtube"></i></a>
            </div>
        </div>

        <nav aria-label="Quick links">
            <h2>Quick Links</h2>
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('explore') }}">Explore</a></li>
                <li><a href="{{ route('articles.index') }}">Articles</a></li>
                <li><a href="{{ route('community.index') }}">Community</a></li>
                <li><a href="{{ route('events.index') }}">Events</a></li>
                <li><a href="{{ route('media.index') }}">Multimedia</a></li>
            </ul>
        </nav>

        <nav aria-label="Fandoms">
            <h2>Fandoms</h2>
            <ul>
                <li><a href="{{ route('category.show', 'anime') }}">Anime</a></li>
                <li><a href="{{ route('category.show', 'gaming') }}">Gaming</a></li>
                <li><a href="{{ route('category.show', 'movies') }}">Movies</a></li>
                <li><a href="{{ route('category.show', 'tv-shows') }}">TV Shows</a></li>
                <li><a href="{{ route('category.show', 'k-pop') }}">K-Pop</a></li>
                <li><a href="{{ route('category.show', 'comics') }}">Comics</a></li>
                <li><a href="{{ route('category.show', 'manga') }}">Manga</a></li>
                <li><a href="{{ route('category.show', 'cosplay') }}">Cosplay</a></li>
            </ul>
        </nav>

        <nav aria-label="Legal">
            <h2>Legal</h2>
            <ul>
                <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}">Terms &amp; Conditions</a></li>
                <li><a href="{{ route('sitemap') }}">Sitemap</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </nav>

        <form class="fh-footer-news" method="POST" action="{{ route('newsletter.store') }}">
            @csrf
            <h2>Subscribe to Our Newsletter</h2>
            <p>Get the latest updates, releases and events.</p>
            <div class="fh-news">
                <label class="visually-hidden" for="footer-newsletter-email">Email address</label>
                <input
                    id="footer-newsletter-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    autocomplete="email"
                    required
                    @if($errors->has('email')) aria-invalid="true" aria-describedby="footer-newsletter-error" @endif
                >
                <button class="btn btn-fh" type="submit" aria-label="Subscribe">
                    <i class="bi bi-send-fill" aria-hidden="true"></i>
                </button>
            </div>
            @error('email')
                <p class="fh-news-error" id="footer-newsletter-error">{{ $message }}</p>
            @enderror
        </form>
    </div>

    <div class="fh-footer-bottom">
        <span>© {{ date('Y') }} Fan Hub Plus. All rights reserved.</span>
        <span>Explore. Discover. Connect.</span>
    </div>
</footer>
