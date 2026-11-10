<?php

namespace Database\Seeders;

use App\Models\AidCenter;
use App\Models\Demarche;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BrazilSejourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. DÉMARCHES ADMINISTRATIVES AVEC GUIDES COMPLETS (Questions, Checklist, Étapes, FAQ)
        $cpfQuestions = [
            [
                'id' => 'q_location',
                'question' => '1. Où vous trouvez-vous actuellement ?',
                'subtitle' => 'La méthode de dépôt du dossier varie selon que vous êtes au Brésil ou encore à l\'étranger.',
                'options' => [
                    [
                        'id' => 'in_brazil',
                        'label' => 'Déjà au Brésil (sur le territoire national)',
                        'detail' => 'Option d\'envoi en ligne par e-mail régional ou dépôt en agence Correios / Banco do Brasil.'
                    ],
                    [
                        'id' => 'abroad',
                        'label' => 'Encore à l\'étranger (avant mon voyage)',
                        'detail' => 'Demande 100% en ligne et gratuite par e-mail dédié auprès de la Receita Federal.'
                    ]
                ]
            ],
            [
                'id' => 'q_document',
                'question' => '2. De quel document d\'identité disposez-vous ?',
                'subtitle' => 'Une pièce officielle lisible avec filiation (noms des parents) est exigée par la loi.',
                'options' => [
                    [
                        'id' => 'passport',
                        'label' => 'Passeport valide de mon pays d\'origine',
                        'detail' => 'Recommandé. Vérifiez que la filiation (noms des parents) y figure ou préparez un acte de naissance.'
                    ],
                    [
                        'id' => 'mercosur_id',
                        'label' => 'Carte nationale d\'identité d\'un pays d\'Amérique du Sud (Mercosul)',
                        'detail' => 'Valable pour les citoyens d\'Argentine, Uruguay, Paraguay, Chili, Colombie, Pérou, etc.'
                    ],
                    [
                        'id' => 'refugee_protocol',
                        'label' => 'Protocole de demandeur d\'asile ou de réfugié (CONARE)',
                        'detail' => 'Document 100% officiel au Brésil, gratuit et prioritaire auprès des services publics.'
                    ]
                ]
            ],
            [
                'id' => 'q_profile',
                'question' => '3. Quelle est votre situation principale ?',
                'subtitle' => 'Permet d\'identifier si vous bénéficiez d\'exemptions de frais ou de parcours prioritaires.',
                'options' => [
                    [
                        'id' => 'residence_work',
                        'label' => 'Installation, travail, études ou tourisme',
                        'detail' => 'Parcours de droit commun auprès de la Receita Federal.'
                    ],
                    [
                        'id' => 'humanitarian_refugee',
                        'label' => 'Réfugié, demandeur d\'asile ou accueil humanitaire',
                        'detail' => 'Exemption légale de toute taxe et accompagnement par les CRAI et Cáritas.'
                    ]
                ]
            ]
        ];

        $cpfChecklist = [
            [
                'id' => 'doc_passport',
                'label' => 'Passeport en cours de validité (ou pièce d\'identité officielle)',
                'isMandatory' => true,
                'description' => 'Copie nette et lisible de la page avec photo, signature et données d\'état civil.',
                'tip' => 'Pour les ressortissants du Mercosur et pays associés, la carte d\'identité nationale est acceptée.'
            ],
            [
                'id' => 'doc_birth_certificate',
                'label' => 'Acte de naissance ou livret de famille (avec nom complet des parents)',
                'isMandatory' => true,
                'description' => 'La législation fiscale brésilienne exige obligatoirement le nom complet des parents (filiation) pour créer un registre CPF.',
                'tip' => 'Si votre passeport mentionne le nom de vos parents, il suffit souvent pour la demande.'
            ],
            [
                'id' => 'doc_fcpf_form',
                'label' => 'Formulaire d\'inscription FCPF (rempli en ligne)',
                'isMandatory' => true,
                'description' => 'Généré sur le portail officiel de la Receita Federal. Doit être sauvegardé en PDF ou imprimé.',
                'tip' => 'Conservez précieusement le numéro de protocole généré à la fin du formulaire.'
            ],
            [
                'id' => 'doc_proof_address',
                'label' => 'Justificatif de domicile (ou déclaration sur l\'honneur)',
                'isMandatory' => false,
                'description' => 'Facture, contrat de location, réservation temporaire ou attestation d\'hébergement signée.',
                'tip' => 'Si vous venez d\'arriver, l\'adresse provisoire de votre hôtel, auberge ou hébergeant est acceptée.'
            ],
            [
                'id' => 'doc_selfie',
                'label' => 'Photo selfie tenant le document d\'identité (si demande par e-mail)',
                'isMandatory' => false,
                'description' => 'Photo nette de votre visage à côté de la page photo de votre document d\'identité ouvert.',
                'tip' => 'Exigé uniquement pour les dépôts par e-mail afin de prévenir les fraudes d\'usurpation.'
            ]
        ];

        $cpfSteps = [
            [
                'number' => 1,
                'title' => 'Remplir le formulaire officiel en ligne (FCPF)',
                'badge' => 'Étape obligatoire',
                'description' => 'Accédez au formulaire officiel de la Receita Federal. Renseignez avec exactitude vos nom, prénoms, nationalité et la filiation complète (noms des deux parents). Validez pour générer votre numéro de protocole.',
                'officialUrl' => 'https://servicos.receita.fazenda.gov.br/Servicos/CPF/InscricaoPublica/inscricao.asp',
                'officialUrlLabel' => 'Accéder au formulaire FCPF (gov.br)'
            ],
            [
                'number' => 2,
                'title' => 'Transmettre votre dossier (Par e-mail gratuit ou au guichet)',
                'badge' => 'Gratuit par mail ou 7 R$ en bureau',
                'description' => "Option A (Gratuit) : Envoyez vos pièces scannées par e-mail à l'adresse dédiée de la région fiscale (ex: cpf.residente.exterior@rfb.gov.br si vous êtes à l'étranger, ou l'e-mail de votre État au Brésil).\nOption B (Présentiel, 7 R$) : Présentez votre numéro de protocole dans un bureau de poste Correios, Banco do Brasil ou Caixa Econômica Federal.",
                'officialUrl' => 'https://www.gov.br/receitafederal/pt-br/assuntos/orientacao-tributaria/cadastros/cpf/servicos-cpf-por-e-mail',
                'officialUrlLabel' => 'Consulter les adresses e-mail régionales'
            ],
            [
                'number' => 3,
                'title' => 'Télécharger et conserver votre attestation CPF (PDF)',
                'badge' => 'Document final officiel',
                'description' => "Dès validation par la Receita Federal, vous recevrez votre numéro de CPF (format 000.000.000-00). Vous pouvez imprimer et télécharger votre attestation officielle (Comprovante de Inscrição) directement sur le site gov.br. La carte plastique bleue n'est plus délivrée.",
                'officialUrl' => 'https://servicos.receita.fazenda.gov.br/Servicos/CPF/ImpressaoComprovante/ConsultaImpressao.asp',
                'officialUrlLabel' => 'Éditer mon attestation de CPF (gov.br)'
            ]
        ];

        $cpfFaqs = [
            [
                'question' => 'Le CPF me donne-t-il le droit de résider au Brésil ?',
                'answer' => "Non. Le CPF est un identifiant fiscal, semblable au numéro d'identification fiscale ou de sécurité sociale. Il n'autorise pas en lui-même la résidence et ne remplace pas le visa ou le registre auprès de la Polícia Federal. Cependant, tout étranger a le droit légal d'en posséder un, même en séjour temporaire ou touriste."
            ],
            [
                'question' => 'Combien coûte l\'obtention du CPF ?',
                'answer' => "La demande est 100% gratuite si vous la faites en ligne et par e-mail auprès de la Receita Federal, ou auprès d'un centre d'accueil pour migrants (CRAI). Si vous choisissez de faire valider votre inscription au guichet d'un bureau de poste (Correios) ou d'une banque publique, une taxe de service forfaitaire de 7,00 R$ vous sera demandée."
            ],
            [
                'question' => 'Puis-je demander mon CPF avant d\'arriver au Brésil ?',
                'answer' => "Oui ! C'est même vivement recommandé. Toute personne résidant à l'étranger peut remplir le formulaire en ligne et envoyer son dossier par courriel à cpf.residente.exterior@rfb.gov.br. Vous recevrez votre attestation PDF avant même d'embarquer."
            ],
            [
                'question' => 'Existe-t-il encore une carte physique en plastique pour le CPF ?',
                'answer' => "Non. Depuis plusieurs années, l'État brésilien a totalement dématérialisé le CPF. La seule preuve officielle est le « Comprovante de Inscrição » au format PDF avec code QR de vérification, ou l'affichage de votre numéro dans l'application mobile gov.br."
            ],
            [
                'question' => 'Que faire si le nom de mes parents ne figure pas sur mon passeport ?',
                'answer' => "Fournissez simplement une copie de votre acte de naissance ou de votre livret de famille où figurent les noms complets de vos parents. Si votre document est rédigé en français, anglais ou espagnol, la Receita Federal l'accepte généralement pour l'inscription au CPF sans exiger de traduction assermentée."
            ]
        ];

        // Mise à jour ou création CPF
        Demarche::updateOrCreate(
            ['id' => 1],
            [
                'step_number' => 'ÉTAPE 01',
                'priority' => 'Priorité 1',
                'icon' => 'credit-card',
                'title' => 'CPF (Numéro fiscal)',
                'subtitle' => 'La clé de voûte de toute votre vie quotidienne et administrative au Brésil',
                'description' => 'Indispensable dès le premier jour : acheter une puce téléphonique brésilienne, ouvrir un compte bancaire, louer un logement, payer instantanément avec Pix ou souscrire un contrat.',
                'delay' => 'Immédiat en bureau · 24 à 72h en ligne',
                'cost' => '100% Gratuit en ligne / par e-mail · 7,00 R$ en agence',
                'button_text' => 'Voir le guide officiel',
                'guide_url' => 'https://www.gov.br/receitafederal/pt-br/assuntos/meu-cpf',
                'official_source' => 'Receita Federal do Brasil (Ministério da Fazenda)',
                'questions' => $cpfQuestions,
                'checklist' => $cpfChecklist,
                'steps' => $cpfSteps,
                'faqs' => $cpfFaqs,
                'status' => 'active',
                'order' => 1,
            ]
        );

        // 2. CENTRES D'ACCUEIL
        // 3. UTILISATEURS & RÔLES (ADMIN & UTILISATEUR)
        User::updateOrCreate(
            ['email' => 'admin@sejour-bresil.org'],
            [
                'name' => 'Administrateur Fédéral',
                'role' => 'admin',
                'password' => Hash::make('admin2026'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@sejour-bresil.org'],
            [
                'name' => 'Usager Migrant',
                'role' => 'user',
                'password' => Hash::make('user2026'),
            ]
        );
    }
}
