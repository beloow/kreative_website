<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        string $contactRecipient = null,
    ): Response {
        $contactMessage = new ContactMessage();
        $form = $this->createForm(ContactType::class, $contactMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1. On sauvegarde la demande en base pour le suivi côté admin
            $entityManager->persist($contactMessage);
            $entityManager->flush();

            // 2. On notifie l'équipe par e-mail
            $recipient = $_ENV['CONTACT_RECIPIENT'] ?? 'contact@kreative-studio.fr';

            $email = (new Email())
                ->from('site@kreative-studio.fr')
                ->to($recipient)
                ->replyTo($contactMessage->getEmail())
                ->subject('Nouvelle demande de contact — '.$contactMessage->getFullName())
                ->text(sprintf(
                    "Nom : %s\nEmail : %s\nEntreprise : %s\n\nMessage :\n%s",
                    $contactMessage->getFullName(),
                    $contactMessage->getEmail(),
                    $contactMessage->getCompany() ?? 'Non renseignée',
                    $contactMessage->getMessage()
                ));

            try {
                $mailer->send($email);
            } catch (\Throwable $e) {
                // L'envoi d'e-mail a échoué mais la demande est déjà enregistrée en base.
                // On informe quand même l'utilisateur que sa demande est bien reçue.
            }

            $this->addFlash('success', 'Merci ! Votre message a bien été envoyé, nous vous répondons sous 24h.');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
