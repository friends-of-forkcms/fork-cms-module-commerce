<?php

namespace Backend\Modules\Commerce\Domain\Order\Mailer;

use Common\Uri;
use Frontend\Core\Engine\Model;
use Swift_Attachment;
use Swift_Message;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * This class will send mails.
 */
class Message extends Swift_Message
{
    public function __construct(
        string $subject = null,
        string $body = null,
        string $contentType = null,
        string $charset = null
    ) {
        parent::__construct($subject, $body, $contentType, $charset);
    }

    /**
     * Create a new Message.
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null): Message
    {
        return new self($subject, $body, $contentType, $charset);
    }

    /**
     * Parses a TwigTemplate with the wanted variables.
     */
    public function parseHtml(string $template, array $variables, bool $addUTM = false): self
    {
        $html = $this->getTemplateContent($template, $variables);
        $html = $this->relativeToAbsolute($html);
        $html = $this->cssToInlineStyles($html);

        if ($addUTM === true) {
            $html = $this->addUTM($html, $this->getSubject());
        }

        $this->setBody($html, 'text/html');

        return $this;
    }

    /**
     * Attach multiple attachments to this message.
     */
    public function addAttachments(array $attachments): Message
    {
        if (!empty($attachments)) {
            // add attachments one by one
            foreach ($attachments as $attachment) {
                // only add existing files
                if (is_file($attachment)) {
                    $this->attach(Swift_Attachment::fromPath($attachment));
                }
            }
        }

        return $this;
    }

    /**
     * Add plaintext content as fallback for the html.
     */
    public function setPlainText(string $content): Message
    {
        if (!empty($content)) {
            $this->addPart($content, 'text/plain');
        }

        return $this;
    }

    /**
     * @param string $html    the html to convert links in
     * @param string $subject The subject of the mail
     */
    private function addUTM(string $html, string $subject): string
    {
        // match links
        $matches = [];
        preg_match_all('/href="(http:\/\/(.*))"/iU', $html, $matches);

        // any links?
        $utm = [
            'utm_source' => 'mail',
            'utm_medium' => 'email',
            'utm_campaign' => Uri::getUrl($subject),
        ];
        if (isset($matches[0]) && !empty($matches[0])) {
            $searchLinks = [];
            $replaceLinks = [];

            // loop old links
            foreach ($matches[1] as $i => $link) {
                $searchLinks[] = $matches[0][$i];
                $replaceLinks[] = 'href="'.Model::addUrlParameters($link, $utm).'"';
            }

            $html = str_replace($searchLinks, $replaceLinks, $html);
        }

        return $html;
    }

    /**
     * Returns the content from a given template.
     *
     * @param string $template  the template to use
     * @param array  $variables the variables to assign
     */
    private function getTemplateContent(string $template, array $variables = null): string
    {
        return Model::get('templating')->render(
            $template,
            $variables
        );
    }

    /**
     * Converts all css to inline styles.
     */
    private function cssToInlineStyles(string $html): string
    {
        $cssToInlineStyles = new CssToInlineStyles();
        $cssToInlineStyles->setHTML($html);
        $cssToInlineStyles->setUseInlineStylesBlock(true);

        return (string) $cssToInlineStyles->convert();
    }

    /**
     * Replace internal links and images to absolute links.
     *
     * @param string $html the html to convert links in
     */
    private function relativeToAbsolute(string $html): string
    {
        // replace internal links/images
        $search = ['href="/', 'src="/'];
        $replace = ['href="'.SITE_URL.'/', 'src="'.SITE_URL.'/'];

        return str_replace($search, $replace, $html);
    }
}
