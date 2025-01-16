<?php

namespace App\Controller;

use App\Entity\Url;
use App\Form\UrlType;
use App\Repository\UrlRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/shortUrl', name: 'app_short')]
    public function shortUrl(ManagerRegistry $managerRegistry, Request $request): Response
    {
        $user = $this->getUser();
        $url = new Url();
        $form = $this->createForm(UrlType::class, $url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url->setShortUrl(substr(md5(uniqid()), 0, 5));
            $url->setDate(new \DateTime());
            $managerRegistry->getManager()->persist($url);
            $managerRegistry->getManager()->flush();

            $this->addFlash("success", "shortUrl/".$url->getShortUrl());
        }

        return $this->render('page/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/shortUrl/{shorturl}', name: 'app_redirect')]
    public function redirectUrl($shorturl, UrlRepository $urlRepository): Response
    {
        $url = $urlRepository->findOneBy(['shortUrl' => $shorturl]);
        if (!$url) {
            throw $this->createNotFoundException("Url not found");
        } else {
            return $this->redirect($url->getOriginalUrl());
        }
    }
}
