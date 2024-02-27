<?php

namespace App\Controller;

use App\Entity\Conservation;
use App\Form\ConservationType;
use App\Repository\ArchiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FormError;
use APY\DataGridBundle\Grid\Source\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\ActionRender;
use App\Annotation\Module;
use App\Controller\FileTrait;
use App\Repository\ConservationRepository;
use App\Repository\TypeRepository;
use App\Service\Omines\Adapter\ArrayAdapter;

/**
 * @Route("/admin/_admin/conservation")
 */
class ConservationController extends AbstractController
{
    use FileTrait;
    /**
     * @Route("/courrier-conservation", name="conservation")
     * @param TypeRepository $repository
     * @return Response
     */
    public function index(): Response
    {

        return $this->render('_admin/conservation/index.html.twig', [
            /*'etats' => $etats,*/
            'titre' => 'Liste des conservations',
        ]);
    }


    /**
     * @Route("/admin_conservation/liste", name="admin_conservation_liste")
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @param ConservationRepository $conservationRepository
     * @return Response
     */
    public function liste(
        Request $request,
        DataTableFactory $dataTableFactory,
        ConservationRepository $conservationRepository
    ): Response {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $conservationRepository->countAll();
        $totalFilteredData = $conservationRepository->countAll($searchValue);
        $data = $conservationRepository->getAll($limit, $offset,  $searchValue);

        //dd($data);


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ])->setName('dt_');;


        $table->add('code', TextColumn::class, ['label' => 'Code', 'className' => 'w-100px'])
            ->add('Libelle', TextColumn::class, ['label' => 'Libelle',]);





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
                                'url' => $this->generateUrl('app_admin_conservation_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-edit', 'attrs' => ['class' => 'btn-success'], 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            /*  'details' => [
                                'url' => $this->generateUrl('conservation_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-eye', 'attrs' => ['class' => 'btn-primary'], 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ], */
                            'delete' => [
                                'url' => $this->generateUrl('app_admin_conservation_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-trash-2', 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression'],  'render' => new ActionRender(function () use ($renders) {
                                    return $renders['delete'];
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

        return $this->render('_admin/conservation/liste.html.twig', ['datatable' => $table]);
    }

    /**
     * @Route("/new", name="app_admin_conservation_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, FormError $formError, TypeRepository $typeRepository): Response
    {


        $conservation = new Conservation();

        $filePath = 'conservations';
        $form = $this->createForm(ConservationType::class, $conservation, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_admin_conservation_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = 200;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl('conservation');



            if ($form->isValid()) {

                $em->persist($conservation);
                $em->flush();
                $data = true;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = 500;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/conservation/new.html.twig', [
            'conservation' => $conservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="app_admin_conservation_show", methods={"GET"})
     */
    public function show(Conservation $conservation): Response
    {
        return $this->render('_admin/conservation/show.html.twig', [
            'conservation' => $conservation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_admin_conservation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Conservation $conservation, FormError $formError, EntityManagerInterface $em): Response
    {


        $form = $this->createForm(ConservationType::class, $conservation, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_admin_conservation_edit', ['id' =>  $conservation->getId()])
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        $data = null;
        $statutCode = 200;
        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl('conservation');


            if ($form->isValid()) {
                $em->flush();
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = 500;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/conservation/edit.html.twig', [
            'conservation' => $conservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_admin_conservation_delete", methods={"DELETE", "GET"})
     */
    public function delete(Request $request, EntityManagerInterface $em, Conservation $conservation): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_admin_conservation_delete',
                    [
                        'id' => $conservation->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $em->remove($conservation);
            $em->flush();


            $redirect = $this->generateUrl('app_admin_conservation_index');


            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'redirect' => $redirect,
                'data' => $data
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }
        }

        return $this->render('_admin/conservation/delete.html.twig', [
            'conservation' => $conservation,
            'form' => $form->createView(),
        ]);
    }
}
