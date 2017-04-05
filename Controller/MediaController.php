<?php

namespace MediaMonks\SonataMediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends Controller
{
    /**
     * @param Request $request
     * @param int $id
     * @param int $width
     * @param int $height
     * @return RedirectResponse
     */
    public function imageRedirectAction(Request $request, $id, $width, $height)
    {
        $media = $this->getDoctrine()->getManager()->find('MediaMonksSonataMediaBundle:Media', $id);

        return $this->get('mediamonks.sonata_media.utility.image')->getRedirectResponse($media, $width, $height, $request->query->all());
    }
}
