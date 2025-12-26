    </main>
    <footer class="site-footer">
        <div class="wrap footer-grid">
            <div class="footer-brand">
                <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
                    <span class="brand-mark" aria-hidden="true"></span>
                    <?php bloginfo('name'); ?>
                </a>
                <p><?php esc_html_e('Curated destinations, smarter searches, and hotel deals that feel worth the trip.', 'travel'); ?></p>
                <div class="footer-badges">
                    <span class="badge">24/7 support</span>
                    <span class="badge">Local insights</span>
                    <span class="badge">Price alerts</span>
                </div>
            </div>
            <div class="footer-links">
                <h2><?php esc_html_e('Explore', 'travel'); ?></h2>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'menu_class' => 'footer-menu',
                    'container' => false,
                    'fallback_cb' => 'travel_fallback_menu',
                ]);
                ?>
            </div>
            <div class="footer-links">
                <h2><?php esc_html_e('Support', 'travel'); ?></h2>
                <ul class="footer-menu">
                    <li><a href="<?php echo esc_url(home_url('/privacy-policy')); ?>"><?php esc_html_e('Privacy Policy', 'travel'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/terms-and-conditions')); ?>"><?php esc_html_e('Terms', 'travel'); ?></a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h2><?php esc_html_e('Stay in the loop', 'travel'); ?></h2>
                <p><?php esc_html_e('Get fresh deals and destination guides every week.', 'travel'); ?></p>
                <form class="footer-form" action="<?php echo esc_url(home_url('/')); ?>" method="get">
                    <label class="screen-reader-text" for="footer-email"><?php esc_html_e('Email address', 'travel'); ?></label>
                    <input id="footer-email" type="email" name="travel-email" placeholder="support@travelfinderhunter.com" required>
                    <button type="submit" class="button button--compact"><?php esc_html_e('Join', 'travel'); ?></button>
                </form>
            </div>
        </div>
        <div class="wrap footer-bottom">
            <p>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'travel'); ?></p>
            <div class="footer-note">
                <?php esc_html_e('Built for travelers who want clarity, not clutter.', 'travel'); ?>
            </div>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
