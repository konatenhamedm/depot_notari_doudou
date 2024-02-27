<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Client;
use App\Form\CalendarType;
use App\Repository\CalendarRepository;
use App\Service\MailerService;
use App\Service\PaginationService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin")
 */
class CalendarController extends AbstractController
{

    /**
     * @Route("/calendar/{id}/confirmation", name="calendar_confirmation", methods={"GET"})
     * @param $id
     * @param Calendar $parent
     * @return Response
     */
    public function confirmation($id,Calendar $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig',[
            'id'=>$id,
            'action'=>'calendar',
        ]);
    }


    /**
     * @Route("/calendar", name="calendar")
     * @param CalendarRepository $repository
     * @return Response
     */
    public function index(CalendarRepository $repository): Response
    {
        $pagination = $repository->getEventDateValide();
        $datePassee = $repository->getEventDatePasse();
//dd($repository->getEventDatePasse());
        return $this->render('_admin/calendar/index.html.twig', [
            'pagination' => $pagination,
            'passe' => $datePassee,
            'tableau' => ['titre'=>'titre'
                ,'DEBUT'=> 'DEBUT'
                ,'FIN'=> 'FIN'
                //,'description'=> 'description'
                //,'tout'=> 'tout'
            ],
            'modal' => 'modal',
            'titre' => 'Liste des événements',

        ]);
    }
    /**
     * @Route("/calendar/{id}/show", name="calendar_show", methods={"GET"})
     */
    public function show(Calendar $calendar): Response
    {
        $form = $this->createForm(CalendarType::class,$calendar, [
            'method' => 'POST',
            'action' => $this->generateUrl('calendar_show',[
                'id'=>$calendar->getId(),
            ])
        ]);

        return $this->render('_admin/calendar/voir.html.twig', [
            'calendar' => $calendar,
            'titre' => 'Evenement',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar/new", name="calendar_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface  $em,UploaderHelper  $uploaderHelper,MailerService $mailerService): Response
    {
        $calendar = new Calendar();
        $form = $this->createForm(CalendarType::class,$calendar, [
            'method' => 'POST',
            'action' => $this->generateUrl('calendar_new')
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $redirect = $this->generateUrl('calendar');
           // dd($form->getData()->getClient());
            $email= "";
            if ($form->getData()->getClient()->getRaisonSocial() == "") {
                $email =$form->getData()->getClient()->getEmail();
            } else {

                $email =$form->getData()->getClient()->getEmailEntreprise();
            }

            $identite = "";
//dd($form->getData()->getClient());
            if ($form->getData()->getClient()->getRaisonSocial() == ""){
                $identite =$form->getData()->getClient()->getNom()." ".$form->getData()->getClient()->getPrenom() ;
            }else{

                $identite = $form->getData()->getClient()->getRaisonSocial();
            }

            $objet = $form->getData()->getDescription();
            if($form->isValid()){
           /*     $mailerService->send(
                    'INFORMATION CONCERNANT LE RENDEZ-VOUS',
                    'konatenvaly@gmail.com',
                    $email,
                    "_admin/contact/template.html.twig",
                    [
                        'message' =>  $objet,
                        'entreprise' =>  "Notari",
                        'identite' =>  $identite,
                        'telephone' =>  '0704314164'
                    ]
                );*/

                $calendar->setActive(1)
                    ->setAllDay(false)
                    ->setBackgroundColor("#31F74F")
                    ->setBorderColor("#BBF0DA")
                    ->setTextColor("#FAF421")
                ;
                $em->persist($calendar);
                $em->flush();

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

            }
            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/calendar/new.html.twig', [
            'titre' => 'Evenement',
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar/{id}/edit", name="calendar_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Calendar $calendar
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function edit(Request $request,Calendar $calendar, EntityManagerInterface  $em,UploaderHelper  $uploaderHelper): Response
    {

        $form = $this->createForm(CalendarType::class,$calendar, [
            'method' => 'POST',
            'action' => $this->generateUrl('calendar_edit',[
                'id'=>$calendar->getId(),
            ])
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {

            $redirect = $this->generateUrl('calendar');

            if($form->isValid()){

                $em->persist($calendar);
                $em->flush();

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);

            }

            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/calendar/edit.html.twig', [
            'titre' => 'Evenement',
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar/delete/{id}", name="calendar_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Calendar $calendar
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em,Calendar $calendar): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'calendar_delete'
                    ,   [
                        'id' => $calendar->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($calendar);
            $em->flush();

            $redirect = $this->generateUrl('calendar');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'redirect' => $redirect,
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }



        }
        return $this->render('_admin/calendar/delete.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar/{id}/active", name="calendar_active", methods={"GET"})
     * @param $id
     * @param Calendar $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active(Calendar $parent,EntityManagerInterface $entityManager): Response
    {
       // $entityManager = $this->getDoctrine()->getManager();


        if ($parent->getActive() == 1){

            $parent->setActive(0);

        }else{

            $parent->setActive(1);

        }

        $entityManager->persist($parent);
        $entityManager->flush();
        return $this->json([
            'code'=>200,
            'message'=>'ça marche bien',
            'active'=>$parent->getActive(),
        ],200);
    }

}
