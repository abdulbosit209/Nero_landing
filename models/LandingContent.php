<?php

declare(strict_types=1);

namespace app\models;

/**
 * Single source of truth for which items appear in each repeating section of the
 * landing page (services, work samples, advantages, comparison rows, FAQ).
 *
 * Every view that renders one of these sections loops over the matching constant
 * below and looks up its copy via `Yii::t('app', "<section>.$slug.<field>")`, so the
 * actual translated text always lives in messages/{en,ru,uz}/app.php.
 *
 * To add, remove or reorder an item shown on the page:
 *  1. Edit the relevant constant in this class.
 *  2. Add/remove the matching `"<section>.$slug.*"` keys in all three files under
 *     messages/{en,ru,uz}/app.php.
 * No view file needs to change — they all iterate these lists.
 */
final class LandingContent
{
    /**
     * Service categories the shop offers. Drives the "Services" grid, the pricing
     * table, and the lead-capture form's service dropdown (see LeadForm::serviceOptions()).
     *
     * @var string[]
     */
    public const SERVICES = ['dent', 'paint', 'polish', 'glass'];

    /**
     * "Recent work" gallery entries. Currently mirrors SERVICES 1:1 (one showcased
     * job per service category) but is kept as its own constant so a work sample can
     * be added/removed independently of the services list later.
     *
     * @var string[]
     */
    public const WORKS = self::SERVICES;

    /**
     * "Why choose us" advantage cards, in display order.
     *
     * @var string[]
     */
    public const ADVANTAGES = ['warranty', 'certified', 'payAfter', 'flexPay', 'oemParts'];

    /**
     * Inline SVG icon markup shown inside each advantage card's icon tile, keyed by
     * advantage slug. Icons follow the Feather Icons style: 24x24 viewBox, stroke
     * outline in `currentColor`, no fill — see views/site/_advantages.php for how the
     * <svg> wrapper is applied.
     *
     * @var array<string, string>
     */
    public const ADVANTAGE_ICONS = [
        'warranty' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
        'certified' => '<circle cx="12" cy="8" r="6"/><path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/>',
        'payAfter' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>',
        'flexPay' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'oemParts' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
    ];

    /**
     * Rows of the "Nero vs. an average shop" comparison table, in display order.
     *
     * @var string[]
     */
    public const COMPARISON_ROWS = ['comesToYou', 'warranty', 'payAfter', 'oemParts', 'transparentPricing', 'certifiedTechs'];

    /**
     * FAQ entries, in display order. The first entry is expanded by default
     * (see views/site/_faq.php).
     *
     * @var string[]
     */
    public const FAQ_IDS = ['arrival', 'dentNoPaint', 'warranty', 'payment', 'spotRepair'];
}
