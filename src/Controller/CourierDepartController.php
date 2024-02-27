<?php

namespace App\Controller;

use App\Entity\CourierArrive;
use App\Entity\Fichier;
use App\Form\CourierArriveType;
use App\Repository\CourierArriveRepository;
use App\Repository\DocumentCourierRepository;
use App\Service\ActionRender;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin")
 */
class CourierDepartController extends AbstractController
{
    use FileTrait;
    /**
     * @Route("/courierDepart/{id}/confirmation", name="courierDepart_confirmation", methods={"GET"})
     * @param $id
     * @param CourierArrive $parent
     * @return Response
     */
    public function confirmation($id, CourierArrive $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig', [
            'id' => $id,
            'action' => 'courierDepart',
        ]);
    }
    /**
     * @Route("/courrier-depart", name="courierDepart")
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public function depart(CourierArriveRepository $repository): Response
    {

        $depart = $repository->findBy(['type' => 'DEPART', 'etat' => 0, 'active' => 1]);
        $depart_finalise = $repository->findBy(['type' => 'DEPART', 'etat' => 1, 'active' => 1]);

        return $this->render('_admin/depart/index.html.twig', [
            'pagination' => $depart,
            'finalise' => $depart_finalise,
            'tableau' => [
                'numero' => 'numero',
                'Date_de_réception' => 'Date_de_réception',
                'Objet' => 'Objet',
                'Expediteur' => 'Expediteur',
            ],
            'critereTitre' => '',
            'modal' => 'modal',
            'position' => 4,
            'active' => 7,
            'titre' => 'Liste des courriers depart',

        ]);
    }

    /**
     * @Route("/courier_depart/liste", name="courier_depart_liste")
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @param CourierArriveRepository $courierArriveRepository
     * @return Response
     */
    public function liste(
        Request $request,
        DataTableFactory $dataTableFactory,
        CourierArriveRepository $courierArriveRepository
    ): Response {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $courierArriveRepository->countAllDepart();
        $totalFilteredData = $courierArriveRepository->countAllDepart($searchValue);
        $data = $courierArriveRepository->getAllDepart($limit, $offset,  $searchValue);

        //dd($data);


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ])->setName('dt_');;


        $table->add('numero', TextColumn::class, ['label' => 'Numéro', 'className' => 'w-100px'])
            ->add('date_reception', DateTimeColumn::class, ['label' => 'Date de réception', 'format' => 'd-m-Y'])
            ->add('objet', TextColumn::class, ['label' => 'Objet', 'className' => 'w-100px'])
            ->add('expediteur', TextColumn::class, ['label' => 'Expediteur', 'className' => 'w-100px']);




        $renders = [
            'edit' =>  new ActionRender(function () {
                return true;
            }),

            'delete' => new ActionRender(function () {
                return true;
            }),
            'details' => new ActionRender(function () {
                return false;
            }),
            'archive' =>  new ActionRender(function () {
                return true;
            }),
        ];


        $hasActions = false;

        foreach ($renders as $_ => $cb) {
            if ($cb->execute()) {
                $hasActions = true;
                break;
            }
        }


        if ($hasActions) {
            $table->add('id', TextColumn::class, [
                'label' => 'Actions', 'field' => 'id', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, $context) use ($renders) {

                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#extralargemodal1',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('courierDepart_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-edit', 'attrs' => ['class' => 'btn-success'], 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            /*  'details' => [
                                'url' => $this->generateUrl('courierArrive_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-eye', 'attrs' => ['class' => 'btn-primary'], 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ], */
                            'delete' => [
                                'url' => $this->generateUrl('courierDepart_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-trash-2', 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression'],  'render' => new ActionRender(function () use ($renders) {
                                    return $renders['delete'];
                                })
                            ],
                            'archive' => [
                                'url' => $this->generateUrl('courierDepart_archive', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-file', 'attrs' => ['class' => 'btn-dark', 'title' => 'Archive'], 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['archive'];
                                })
                            ],
                        ]
                    ];
                    return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                }
            ]);
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('_admin/depart/liste.html.twig', ['datatable' => $table]);
    }

    /**
     * @Route("/archive/{id}/depart", name="courierDepart_archive", methods={"GET"})
     * @param $id
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public  function  archive($id, DocumentCourierRepository $documentCourierRepository)
    {


        return $this->render('_admin/arrive/archive.html.twig', [
            'titre' => 'Depart',
            'data' => $documentCourierRepository->getFichier($id),

        ]);
    }

    /**
     * @Route("/courier-depart/{id}/show", name="courierDepart_show", methods={"GET"})
     * @param CourierArrive $courierArrive
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public function show($id, CourierArrive $courierArrive, CourierArriveRepository $repository): Response
    {
        //$type = $courierArrive->getType();

        $form = $this->createForm(CourierArriveType::class, $courierArrive, [

            'method' => 'POST',
            'action' => $this->generateUrl('courierDepart_show', [
                'id' => $courierArrive->getId(),
            ])
        ]);

        return $this->render('_admin/depart/voir.html.twig', [
            'titre' => 'DEPART',
            // 'data' => $repository->getFichier($id),
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier-depart/new", name="courierDepart_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UploaderHelper $uploaderHelper
     * @param CourierArriveRepository $repository
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper, CourierArriveRepository $repository): Response
    {


        $courierArrive = new CourierArrive();

        $filePath = 'courrier';

        $form = $this->createForm(CourierArriveType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir($filePath, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl('courierDepart_new')
        ]);


        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
        //$type = $form->getData()->getType();
        if ($form->isSubmitted()) {
            $statut = 1;
            $redirect = $this->generateUrl('courierDepart');
            //$brochureFile = $form->get('fichiers')->getData();

            //    dd($brochureFile);
            if ($form->isValid()) {
                //get('image_prod')->getData();

                /*   foreach ($brochureFile as $image) {
                    $file = new File($image->getPath());
                    $newFilename = md5(uniqid()) . '.' . $file->guessExtension();
                    // $fileName = md5(uniqid()).'.'.$file->guessExtension();
                    $file->move($this->getParameter('images_directory'), $newFilename);
                    $image->setPath($newFilename);
                } */
                $courierArrive->setEtat(false);
                $courierArrive->setType('DEPART');
                $courierArrive->setCategorie('COURRIER');
                $courierArrive->setActive(1);
                $em->persist($courierArrive);
                $em->flush();

                $data = true;
                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            }
            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/depart/new.html.twig', [
            'titre' => 'DEPART',
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier-depart/{id}/edit", name="courierDepart_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit($id, Request $request, CourierArrive $courierArrive, EntityManagerInterface $em, CourierArriveRepository $repository): Response
    {
        $filePath = 'courrier';

        $form = $this->createForm(CourierArriveType::class, $courierArrive, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir($filePath, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl('courierDepart_edit', [
                'id' => $courierArrive->getId(),
            ])
        ]);
        /*->get('fichiers')->setData($courierArrive->getFichiers())*/
        /*dd( $form);*/
        $form->handleRequest($request);

        /*  foreach ($courierArrive->getFichiers() as $fichier) {
            $courierArrive->removeFichier($fichier);
        } */

        /*$courierArrive->removeFichier()*/
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
        // $type = $form->getData()->getType();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierDepart');
            //$brochureFile = $form->get('fichiers')->getData();

            if ($form->isValid()) {

                /*  foreach ($brochureFile as $image) {
                    $file = new File($image->getPath());
                    $newFilename = md5(uniqid()) . '.' . $file->guessExtension();
                    // $fileName = md5(uniqid()).'.'.$file->guessExtension();
                    $file->move($this->getParameter('images_directory'), $newFilename);
                    $image->setPath($newFilename);
                } */
                $em->persist($courierArrive);
                $em->flush();
                $data = true;
                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            }

            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/depart/edit.html.twig', [
            'titre' => 'DEPART',
            //'data' => $repository->getFichier($id),
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/courier-depart/{id}/accuse", name="courierDepart_recep", methods={"GET","POST"})
     * @param Request $request
     * @param CourierArrive $courierArrive
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function accuse(Request $request, CourierArrive $courierArrive, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CourierArriveType::class, $courierArrive, [
            'method' => 'POST',
            'action' => $this->generateUrl(
                'courierDepart_recep',
                [
                    'id' => $courierArrive->getId(),
                ]
            )
        ]);

        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();
        //   $type = $form->getData()->getType();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('courierDepart');
            //  $brochureFile = $form->get('fichiers')->getData();

            if ($form->isValid()) {

                /*   foreach ($brochureFile as $image) {
                    $file = new File($image->getPath());
                    $newFilename = md5(uniqid()) . '.' . $file->guessExtension();
                    // $fileName = md5(uniqid()).'.'.$file->guessExtension();
                    $file->move($this->getParameter('images_directory'), $newFilename);
                    $image->setPath($newFilename);
                } */
                $em->persist($courierArrive);
                $em->flush();

                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            }

            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/arrive/accuse.html.twig', [
            'titre' => "ACCUSE DE RECEPTION",
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/courier-depart/delete/{id}", name="courierDepart_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param courierArrive $courierArrive
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, CourierArrive $courierArrive): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'courierDepart_delete',
                    [
                        'id' => $courierArrive->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($courierArrive);
            $em->flush();

            $redirect = $this->generateUrl('courierDepart');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut' => 1,
                'message' => $message,
                'redirect' => $redirect,
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }
        }
        return $this->render('_admin/depart/delete.html.twig', [
            'courierArrive' => $courierArrive,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/liste_tarife", name="liste_tarife_index", methods={"GET","POST"})
     * @param Request $request
     * @param DepartementRepository $repository
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function remplirSelect2Action(Request $request, EntityManagerInterface $em): Response
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) { // pour vérifier la présence d'une requete Ajax

            $id = "";
            $id = $request->get('id');

            if ($id) {

                $ensembles = $repository->listeDepartement($id);

                $arrayCollection = array();

                foreach ($ensembles as $item) {
                    $arrayCollection[] = array(
                        'id' => $item->getId(),
                        'libelle' => $item->getLibDepartement(),
                        // ... Same for each property you want
                    );
                }
                $data = json_encode($arrayCollection); // formater le résultat de la requête en json
                //dd($data);
                $response->headers->set('Content-TypeActe', 'application/json');
                $response->setContent($data);
            }
        }

        return $response;
    }

    /**
     * @Route("/courier-depart/{id}/active", name="courierDepart_active", methods={"GET"})
     * @param $id
     * @param CourierArrive $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id, CourierArrive $parent, EntityManagerInterface $entityManager): Response
    {

        if ($parent->getActive() == 1) {

            $parent->setActive(0);
        } else {

            $parent->setActive(1);
        }

        $entityManager->persist($parent);
        $entityManager->flush();
        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'active' => $parent->getActive(),
        ], 200);
    }

    /**
     * @Route("/existe_depart", name="exsite_depart", methods={"GET","POST"})
     * @param CourierArriveRepository $repository
     * @param Request $request
     * @return Response
     */
    public function existeDepart(CourierArriveRepository $repository, Request $request): Response
    {
        $response = new Response();
        $format = "";
        if ($request->isXmlHttpRequest()) {
            $nombre = $repository->getNombre();

            $date = date('y');


            $format = $date . '-' . $nombre . ' ' . 'D';


            $arrayCollection[] = array(
                'nom' =>  $format,

                // ... Same for each property you want
            );
            $data = json_encode($arrayCollection); // formater le résultat de la requête en json
            //dd($data);
            $response->headers->set('Content-TypeActe', 'application/json');
            $response->setContent($data);
        }
        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'nom' => $format,
        ], 200);
    }
}
