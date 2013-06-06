<?php

namespace Debril\RssAtomBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\OptionsResolver\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Debril\RssAtomBundle\Provider\FeedContentProvider;

class StreamController extends Controller
{

    /**
     * @Route("/stream/{contentId}")
     * @Template()
     */
    public function indexAction($format, $contentId = null)
    {
        $options = new Options;
        if (!is_null($contentId))
            $options->set('contentId', $contentId);

        return $this->createStreamResponse($options, $format);
    }

    /**
     *
     * @param \Symfony\Component\OptionsResolver\Options $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createStreamResponse(Options $options, $format)
    {
        $formatter = $this->getFormatter($format);
        $content = $this->getContent($options);

        $response = new Response($formatter->toString($content));
        $response->headers->set('Content-Type', 'application/xhtml+xml');

        return $response;
    }

    /**
     *
     * @param \Symfony\Component\OptionsResolver\Options $options
     * @return FeedContent
     * @throws \Exception
     */
    protected function getContent(Options $options)
    {
        $provider = $this->get('FeedContentProvider');

        if (!$provider instanceof FeedContentProvider)
        {
            throw new \Exception('Provider is not a FeedContentProvider instance');
        }
        return $provider->getFeedContentById($options);
    }

    /**
     *
     * @param  string $format
     * @return Debril\RssAtomBundle\Protocol\FeedFormatter
     * @throws Exception
     */
    protected function getFormatter($format)
    {
        $services = array(
            'rss' => 'FeedFormatter',
            'atom' => 'FeedFormatter',
        );

        if (!array_key_exists($format, $services))
            throw new Exception("Unsupported format {$format}");

        return $this->get($services[$format]);
    }

}
