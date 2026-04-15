<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\ContactMessage;
use App\Entity\FavoriteList;
use App\Entity\Lesson;
use App\Entity\Resource;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ContactMessageRepository;
use App\Repository\FavoriteListRepository;
use App\Repository\LessonRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-demo-content',
    description: 'Ajoute un jeu de données de démonstration cohérent sans vider la base',
)]
class SeedDemoContentCommand extends Command
{
    private const DEMO_PASSWORD = 'DemoMiftah!2026';

    /**
     * @var array<string, int>
     */
    private array $stats = [
        'users_created' => 0,
        'users_updated' => 0,
        'categories_created' => 0,
        'tags_created' => 0,
        'lessons_created' => 0,
        'lessons_updated' => 0,
        'contacts_created' => 0,
        'favorites_created' => 0,
    ];

    /**
     * @var array<string, Category>
     */
    private array $categoryMap = [];

    /**
     * @var array<string, Tag>
     */
    private array $tagMap = [];

    /**
     * @var array<string, User>
     */
    private array $userMap = [];

    /**
     * @var array<string, Lesson>
     */
    private array $lessonMap = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly FavoriteListRepository $favoriteListRepository,
        private readonly ContactMessageRepository $contactMessageRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Remplissage de démonstration pour Miftah Arabi');

        $adminUser = $this->seedUsers();
        $this->seedCategories();
        $this->seedTags();
        $this->seedLessons($adminUser);
        $this->seedContacts();
        $this->seedFavorites();

        $this->entityManager->flush();

        $io->success('Le contenu de démonstration a bien été préparé.');
        $io->section('Résumé');
        $io->definitionList(
            ['Utilisateurs créés', (string) $this->stats['users_created']],
            ['Utilisateurs mis à jour', (string) $this->stats['users_updated']],
            ['Catégories créées', (string) $this->stats['categories_created']],
            ['Tags créés', (string) $this->stats['tags_created']],
            ['Leçons créées', (string) $this->stats['lessons_created']],
            ['Leçons mises à jour', (string) $this->stats['lessons_updated']],
            ['Messages de contact créés', (string) $this->stats['contacts_created']],
            ['Favoris créés', (string) $this->stats['favorites_created']],
        );

        $io->section('Comptes de démonstration');
        $io->listing([
            'Admin demo : demo.admin@miftah-arabi.local',
            'Élève demo 1 : demo.aicha@miftah-arabi.local',
            'Élève demo 2 : demo.youssef@miftah-arabi.local',
            'Mot de passe commun : '.self::DEMO_PASSWORD,
        ]);

        return Command::SUCCESS;
    }

    private function seedUsers(): User
    {
        $users = [
            [
                'email' => 'demo.admin@miftah-arabi.local',
                'firstName' => 'Admin',
                'lastName' => 'Demo',
                'roles' => ['ROLE_ADMIN'],
            ],
            [
                'email' => 'demo.aicha@miftah-arabi.local',
                'firstName' => 'Aicha',
                'lastName' => 'Benali',
                'roles' => ['ROLE_USER'],
            ],
            [
                'email' => 'demo.youssef@miftah-arabi.local',
                'firstName' => 'Youssef',
                'lastName' => 'Mansouri',
                'roles' => ['ROLE_USER'],
            ],
        ];

        foreach ($users as $spec) {
            $user = $this->userRepository->findOneBy(['email' => $spec['email']]);
            $isNew = null === $user;

            if ($isNew) {
                $user = new User();
                $user->setCreatedAt(new \DateTimeImmutable('-30 days'));
                $this->entityManager->persist($user);
                ++$this->stats['users_created'];
            } else {
                ++$this->stats['users_updated'];
            }

            $user->setFirstName($spec['firstName']);
            $user->setLastName($spec['lastName']);
            $user->setEmail($spec['email']);
            $user->setRoles($spec['roles']);
            $user->setIsVerified(true);
            $user->setVerifiedAt(new \DateTimeImmutable('-29 days'));
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setPassword($this->passwordHasher->hashPassword($user, self::DEMO_PASSWORD));

            $this->userMap[$spec['email']] = $user;
        }

        return $this->userMap['demo.admin@miftah-arabi.local'];
    }

    private function seedCategories(): void
    {
        $categoryNames = [
            'vocabulaire',
            'grammaire',
            'lecture',
            'expression orale',
            'compréhension',
        ];

        foreach ($categoryNames as $name) {
            $category = $this->categoryRepository->findOneBy(['name' => $name]);

            if (!$category instanceof Category) {
                $category = new Category();
                $category->setName($name);
                $category->setCreatedAt(new \DateTimeImmutable('-25 days'));
                $category->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($category);
                ++$this->stats['categories_created'];
            }

            $this->categoryMap[$name] = $category;
        }
    }

    private function seedTags(): void
    {
        $tagNames = [
            'mot',
            'verbe',
            'nom',
            'salutations',
            'famille',
            'voyage',
            'dialogue',
            'nature',
            'emotions',
            'present',
            'description',
        ];

        foreach ($tagNames as $name) {
            $tag = $this->tagRepository->findOneBy(['name' => $name]);

            if (!$tag instanceof Tag) {
                $tag = new Tag();
                $tag->setName($name);
                $tag->setCreatedAt(new \DateTimeImmutable('-24 days'));
                $tag->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($tag);
                ++$this->stats['tags_created'];
            }

            $this->tagMap[$name] = $tag;
        }
    }

    private function seedLessons(User $author): void
    {
        $lessonSpecs = [
            [
                'lookupTitles' => ['Construire des phrases simples au présent', 'Construire des phrases simples au present', 'cours 1'],
                'title' => 'Construire des phrases simples au présent',
                'category' => 'grammaire',
                'tags' => ['verbe', 'nom', 'present'],
                'description' => 'Apprenez à construire des phrases simples avec un sujet, un verbe et un complément.',
                'keywords' => 'phrase simple, grammaire arabe, présent, sujet verbe complément',
                'level' => 'intermediaire',
                'publishedAt' => '-18 days',
                'image' => [
                    'source' => 'al-madaniyya-definition-2-69c83be43f125771764683.png',
                    'target' => 'phrases-present-demo.png',
                ],
                'content' => <<<'HTML'
<h2>Objectif de la leçon</h2>
<p>Dans cette leçon, nous travaillons la construction d'une phrase simple en arabe avec une structure claire.</p>
<h3>Points à retenir</h3>
<ul>
    <li>Identifier le sujet de la phrase.</li>
    <li>Choisir un verbe simple au présent.</li>
    <li>Ajouter un complément court pour enrichir le sens.</li>
</ul>
<p><strong>Exemple :</strong> ana aktubu darsan. Cette phrase signifie : j'écris une leçon.</p>
<p>L'idée est d'apprendre à produire des phrases courtes, justes et faciles à réutiliser dans d'autres contextes.</p>
HTML,
                'resources' => [
                    [
                        'title' => 'Exercice de phrases simples',
                        'type' => 'pdf',
                        'url' => 'https://drive.google.com/file/d/12JnO6HR9O6NvR7_eTBB91vUbmka0T-Lc/view?usp=drive_link',
                    ],
                ],
            ],
            [
                'lookupTitles' => ['Les salutations essentielles en arabe'],
                'title' => 'Les salutations essentielles en arabe',
                'category' => 'vocabulaire',
                'tags' => ['mot', 'salutations', 'dialogue'],
                'description' => 'Mémorisez les salutations les plus utiles pour commencer un échange simple en arabe.',
                'keywords' => 'salutations arabes, vocabulaire arabe, débutant, dialogue',
                'level' => 'debutant',
                'publishedAt' => '-10 days',
                'image' => [
                    'source' => 'al-madaniyya-definition-2-69c83be43f125771764683.png',
                    'target' => 'salutations-demo.png',
                ],
                'content' => <<<'HTML'
<h2>Pourquoi commencer par les salutations ?</h2>
<p>Les salutations permettent de prendre la parole avec confiance et de mémoriser des formulations utiles très tôt dans l'apprentissage.</p>
<h3>Expressions utiles</h3>
<ul>
    <li>as-salaamu alaykum</li>
    <li>wa alaykum as-salaam</li>
    <li>marhaban</li>
    <li>ila al-liqaa</li>
</ul>
<p>S'entraîner à haute voix aide à gagner en fluidité et à mieux retenir la prononciation.</p>
HTML,
                'resources' => [
                    [
                        'title' => 'Video de revision des salutations',
                        'type' => 'video',
                        'url' => 'https://www.youtube.com/embed/vDwqfe-dxdE',
                    ],
                ],
            ],
            [
                'lookupTitles' => ['Parler de sa famille'],
                'title' => 'Parler de sa famille',
                'category' => 'vocabulaire',
                'tags' => ['famille', 'mot', 'nom'],
                'description' => 'Apprenez le vocabulaire de base pour présenter les membres de votre famille en arabe.',
                'keywords' => 'famille en arabe, vocabulaire arabe, débutant',
                'level' => 'debutant',
                'publishedAt' => '-8 days',
                'image' => [
                    'source' => 'enfant-69c062f55b239394528109.jpg',
                    'target' => 'famille-demo.jpg',
                ],
                'content' => <<<'HTML'
<h2>Vocabulaire de base</h2>
<p>Cette leçon vous aide à nommer les membres de la famille et à faire des phrases très simples.</p>
<ul>
    <li>ab : pere</li>
    <li>umm : mere</li>
    <li>akh : frere</li>
    <li>ukht : soeur</li>
</ul>
<p><strong>Exemple :</strong> hadha akhi. Cette phrase signifie : voici mon frère.</p>
HTML,
                'resources' => [
                    [
                        'title' => 'Fiche PDF - vocabulaire de la famille',
                        'type' => 'pdf',
                        'url' => 'https://drive.google.com/file/d/12JnO6HR9O6NvR7_eTBB91vUbmka0T-Lc/view?usp=drive_link',
                    ],
                ],
            ],
            [
                'lookupTitles' => ['Le vocabulaire du voyage et des déplacements', 'Le vocabulaire du voyage et des deplacements'],
                'title' => 'Le vocabulaire du voyage et des déplacements',
                'category' => 'vocabulaire',
                'tags' => ['voyage', 'dialogue', 'mot'],
                'description' => 'Découvrez des mots utiles pour parler d un trajet, d un lieu et d un déplacement simple.',
                'keywords' => 'voyage en arabe, vocabulaire arabe, transport, déplacement',
                'level' => 'intermediaire',
                'publishedAt' => '-5 days',
                'image' => [
                    'source' => 'plage-699b2cf7eb253316722268.webp',
                    'target' => 'voyage-demo.webp',
                ],
                'content' => <<<'HTML'
<h2>Se repérer pendant un trajet</h2>
<p>Le vocabulaire du voyage permet de demander une direction, de parler d'un départ ou de décrire une destination.</p>
<ul>
    <li>safar : voyage</li>
    <li>matar : aeroport</li>
    <li>tariq : route</li>
    <li>mahattah : station</li>
</ul>
<p>Cette leçon vous aide à mémoriser des mots utiles pour comprendre des situations courantes.</p>
HTML,
                'resources' => [
                    [
                        'title' => 'Playlist de revision vocabulaire',
                        'type' => 'video',
                        'url' => 'https://www.youtube.com/embed/videoseries?list=PLF0J9-gTUX2Dp4xz6BsuK1W8D-siUtGHD',
                    ],
                ],
            ],
            [
                'lookupTitles' => ['Exprimer ses émotions simplement', 'Exprimer ses emotions simplement'],
                'title' => 'Exprimer ses émotions simplement',
                'category' => 'expression orale',
                'tags' => ['emotions', 'dialogue', 'mot'],
                'description' => 'Apprenez à exprimer un sentiment simple dans un échange du quotidien.',
                'keywords' => 'émotions en arabe, expression orale, vocabulaire arabe',
                'level' => 'intermediaire',
                'publishedAt' => '-3 days',
                'image' => [
                    'source' => 'yeux-qui-pleurent-69c063f697884570482028.jpg',
                    'target' => 'emotions-demo.jpg',
                ],
                'content' => <<<'HTML'
<h2>Parler de ce que l'on ressent</h2>
<p>Exprimer une emotion permet de construire des phrases plus personnelles et plus naturelles.</p>
<ul>
    <li>sa id : heureux</li>
    <li>hazin : triste</li>
    <li>muta hammad : motive</li>
    <li>ta ban : fatigue</li>
</ul>
<p><strong>Exemple :</strong> ana sa id al-yawm. Cette phrase signifie : je suis heureux aujourd'hui.</p>
HTML,
                'resources' => [],
            ],
            [
                'lookupTitles' => ['Décrire un lieu et son environnement', 'Decrire un lieu et son environnement'],
                'title' => 'Décrire un lieu et son environnement',
                'category' => 'lecture',
                'tags' => ['nature', 'description', 'nom'],
                'description' => 'Travaillez le vocabulaire de la description pour présenter un lieu, un décor ou un environnement.',
                'keywords' => 'description en arabe, lecture, vocabulaire nature',
                'level' => 'avance',
                'publishedAt' => '-2 days',
                'image' => [
                    'source' => 'unnamed-69c063a125688378979306.webp',
                    'target' => 'environnement-demo.webp',
                ],
                'content' => <<<'HTML'
<h2>Lire et décrire</h2>
<p>Cette leçon aide à enrichir le vocabulaire descriptif pour mieux comprendre un texte et mieux présenter un lieu.</p>
<ul>
    <li>hadiqah : jardin</li>
    <li>shajarah : arbre</li>
    <li>sama : ciel</li>
    <li>ard : terre</li>
</ul>
<p>Le travail de lecture peut ensuite être prolongé par une courte production écrite ou orale.</p>
HTML,
                'resources' => [],
            ],
        ];

        foreach ($lessonSpecs as $spec) {
            $lesson = $this->findLessonByTitles($spec['lookupTitles']);
            $isNew = null === $lesson;

            if ($isNew) {
                $lesson = new Lesson();
                $lesson->setCreatedAt(new \DateTimeImmutable($spec['publishedAt']));
                $this->entityManager->persist($lesson);
                ++$this->stats['lessons_created'];
            } else {
                ++$this->stats['lessons_updated'];
            }

            $lesson->setUser($author);
            $lesson->setCategory($this->categoryMap[$spec['category']]);
            $lesson->setTitle($spec['title']);
            $lesson->setDescription($spec['description']);
            $lesson->setKeywords($spec['keywords']);
            $lesson->setContent($spec['content']);
            $lesson->setLevel($spec['level']);
            $lesson->setIsPublished(true);
            $lesson->setPublishedAt(new \DateTimeImmutable($spec['publishedAt']));
            $lesson->setUpdatedAt(new \DateTimeImmutable());
            $lesson->setImage($this->prepareDemoImage($spec['image']['source'], $spec['image']['target']));

            foreach (iterator_to_array($lesson->getTags()) as $tag) {
                $lesson->removeTag($tag);
            }

            foreach ($spec['tags'] as $tagName) {
                $lesson->addTag($this->tagMap[$tagName]);
            }

            foreach (iterator_to_array($lesson->getResources()) as $resource) {
                $lesson->removeResource($resource);
                $this->entityManager->remove($resource);
            }

            foreach ($spec['resources'] as $resourceSpec) {
                $resource = new Resource();
                $resource->setTitle($resourceSpec['title']);
                $resource->setType($resourceSpec['type']);
                $resource->setUrl($resourceSpec['url']);
                $resource->setCreatedAt(new \DateTimeImmutable($spec['publishedAt']));
                $resource->setUpdatedAt(new \DateTimeImmutable());
                $lesson->addResource($resource);
                $this->entityManager->persist($resource);
            }

            $this->lessonMap[$spec['title']] = $lesson;
        }
    }

    private function seedContacts(): void
    {
        $messages = [
            [
                'name' => 'Aicha Benali',
                'email' => 'demo.aicha@miftah-arabi.local',
                'message' => 'Bonjour, je souhaite savoir par quelle catégorie commencer pour une progression débutant.',
                'createdAt' => '-6 days',
                'isProcessed' => true,
            ],
            [
                'name' => 'Youssef Mansouri',
                'email' => 'demo.youssef@miftah-arabi.local',
                'message' => 'Bonjour, les favoris sont très pratiques. Prévoyez-vous d ajouter davantage de leçons de vocabulaire ?',
                'createdAt' => '-4 days',
                'isProcessed' => false,
            ],
            [
                'name' => 'Karim Haddad',
                'email' => 'karim.demo@miftah-arabi.local',
                'message' => 'Bonjour, je trouve la partie lecture très utile. Est-ce qu une version PDF des exercices est prévue ?',
                'createdAt' => '-2 days',
                'isProcessed' => false,
            ],
        ];

        foreach ($messages as $spec) {
            $message = $this->contactMessageRepository->findOneBy([
                'email' => $spec['email'],
                'message' => $spec['message'],
            ]);

            if ($message instanceof ContactMessage) {
                continue;
            }

            $message = new ContactMessage();
            $message->setName($spec['name']);
            $message->setEmail($spec['email']);
            $message->setMessage($spec['message']);
            $message->setCreatedAt(new \DateTimeImmutable($spec['createdAt']));
            $message->setIsProcessed($spec['isProcessed']);
            $this->entityManager->persist($message);
            ++$this->stats['contacts_created'];
        }
    }

    private function seedFavorites(): void
    {
        $pairs = [
            ['demo.aicha@miftah-arabi.local', 'Les salutations essentielles en arabe'],
            ['demo.aicha@miftah-arabi.local', 'Parler de sa famille'],
            ['demo.aicha@miftah-arabi.local', 'Apprends à lire et écrire'],
            ['demo.youssef@miftah-arabi.local', 'Le vocabulaire du voyage et des déplacements'],
            ['demo.youssef@miftah-arabi.local', 'Exprimer ses émotions simplement'],
            ['demo.youssef@miftah-arabi.local', 'Animaux'],
            ['demo.admin@miftah-arabi.local', 'Décrire un lieu et son environnement'],
        ];

        foreach ($pairs as [$email, $lessonTitle]) {
            $user = $this->getUser($email);
            $lesson = $this->getLesson($lessonTitle);

            if (!$user instanceof User || !$lesson instanceof Lesson) {
                continue;
            }

            $favorite = $this->favoriteListRepository->findOneBy([
                'user' => $user,
                'lesson' => $lesson,
            ]);

            if ($favorite instanceof FavoriteList) {
                continue;
            }

            $favorite = new FavoriteList();
            $favorite->setUser($user);
            $favorite->setLesson($lesson);
            $favorite->setCreatedAt(new \DateTimeImmutable('-1 day'));
            $this->entityManager->persist($favorite);
            ++$this->stats['favorites_created'];
        }
    }

    /**
     * @param list<string> $titles
     */
    private function findLessonByTitles(array $titles): ?Lesson
    {
        foreach ($titles as $title) {
            $lesson = $this->lessonRepository->findOneBy(['title' => $title]);
            if ($lesson instanceof Lesson) {
                return $lesson;
            }
        }

        return null;
    }

    private function getUser(string $email): ?User
    {
        if (isset($this->userMap[$email])) {
            return $this->userMap[$email];
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user instanceof User) {
            $this->userMap[$email] = $user;
        }

        return $user;
    }

    private function getLesson(string $title): ?Lesson
    {
        if (isset($this->lessonMap[$title])) {
            return $this->lessonMap[$title];
        }

        $lesson = $this->lessonRepository->findOneBy(['title' => $title]);

        if ($lesson instanceof Lesson) {
            $this->lessonMap[$title] = $lesson;
        }

        return $lesson;
    }

    private function prepareDemoImage(string $sourceFileName, string $targetFileName): string
    {
        $uploadsDir = $this->kernel->getProjectDir().'/public/images/uploads';
        $sourcePath = $uploadsDir.'/'.$sourceFileName;
        $targetPath = $uploadsDir.'/'.$targetFileName;

        if (!is_file($sourcePath)) {
            throw new \RuntimeException(sprintf('Image source introuvable : %s', $sourceFileName));
        }

        if (!is_file($targetPath) && !copy($sourcePath, $targetPath)) {
            throw new \RuntimeException(sprintf('Impossible de copier l image de démo vers %s', $targetFileName));
        }

        return $targetFileName;
    }
}

