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

        // 1B. DÉMARCHE 2 : CRÉER UN COMPTE BANCAIRE AU BRÉSIL
        $bankQuestions = [
            [
                'id' => 'q_bank_residency',
                'question' => '1. Quelle est votre situation de résidence au Brésil ?',
                'subtitle' => 'Les règles de la Banco Central (Résolution CMN 4.753/2019) distinguent résidents et non-résidents.',
                'options' => [
                    [
                        'id' => 'resident_crnm',
                        'label' => 'Résident au Brésil avec CRNM / RNM (ou protocole Polícia Federal)',
                        'detail' => 'Accès complet aux banques digitales (Nubank, Inter, C6) et agences traditionnelles.'
                    ],
                    [
                        'id' => 'newcomer_pending',
                        'label' => 'Nouvel arrivant ou visa en cours (sans CRNM physique)',
                        'detail' => 'Oriente vers les guichets physiques (Banco do Brasil, Itaú, Caixa) avec passeport + protocole.'
                    ],
                    [
                        'id' => 'non_resident_abroad',
                        'label' => 'Non-résident vivant à l\'étranger',
                        'detail' => 'Nécessite une Conta de Não Residente (ex-CDE) auprès d\'établissements agréés en change.'
                    ]
                ]
            ],
            [
                'id' => 'q_bank_type',
                'question' => '2. Quel type de banque privilégiez-vous ?',
                'subtitle' => 'Chaque établissement applique ses propres contrôles d\'identité (KYC).',
                'options' => [
                    [
                        'id' => 'digital_bank',
                        'label' => 'Banque 100% digitale sans frais (Nubank, Banco Inter, C6 Bank)',
                        'detail' => 'Ouverture sur smartphone, carte sans cotisation et Pix illimité (exige CPF + résidence).'
                    ],
                    [
                        'id' => 'traditional_bank',
                        'label' => 'Banque traditionnelle avec agence (Banco do Brasil, Itaú, Caixa, Bradesco)',
                        'detail' => 'Accompagnement au guichet, réception SWIFT internationale et meilleure prise en charge des protocoles.'
                    ]
                ]
            ],
            [
                'id' => 'q_bank_usage',
                'question' => '3. Quel sera votre usage principal du compte ?',
                'subtitle' => 'Permet d\'identifier le forfait gratuit légal ou les pièces financières requises.',
                'options' => [
                    [
                        'id' => 'daily_pix_salary',
                        'label' => 'Vie quotidienne, Pix, loyer et salaire au Brésil (BRL)',
                        'detail' => 'Droit garanti au forfait gratuit « Serviços Essenciais » de la Banco Central.'
                    ],
                    [
                        'id' => 'international_invest',
                        'label' => 'Virements internationaux SWIFT, épargne ou investissement depuis l\'étranger',
                        'detail' => 'Préparez un justificatif de revenus et d\'origine des fonds (compatibilité change / CDE).'
                    ]
                ]
            ]
        ];

        $bankChecklist = [
            [
                'id' => 'doc_cpf',
                'label' => 'Numéro CPF actif et régulier (Comprovante de Situação Cadastral)',
                'isMandatory' => true,
                'description' => 'Obligatoire dans 100% des banques au Brésil. Aucune ouverture de compte (digital, agence ou non-résident) n\'est possible sans CPF valide.',
                'tip' => 'Téléchargez votre attestation « Situação Regular » sur le portail de la Receita Federal avant de commencer.'
            ],
            [
                'id' => 'doc_identity_rnm',
                'label' => 'Passeport valide + Carte CRNM/RNM (ou Protocole Polícia Federal / DPRNM)',
                'isMandatory' => true,
                'description' => 'Pièce d\'identité officielle du migrant au Brésil. Les banques digitales exigent la CRNM ; en agence traditionnelle, le protocole officiel avec passeport ou le DPRNM (réfugiés) est accepté.',
                'tip' => 'Si une application mobile refuse votre passeport ou protocole par reconnaissance automatique, rendez-vous en agence physique.'
            ],
            [
                'id' => 'doc_proof_address_br',
                'label' => 'Justificatif de domicile (Comprovante de Residência)',
                'isMandatory' => true,
                'description' => 'Facture d\'eau, d\'électricité, internet, contrat de location ou attestation d\'hébergement signée avec preuve d\'adresse de l\'hébergeant.',
                'tip' => 'Pour une ouverture en tant que non-résident depuis l\'étranger (CDE), un justificatif du pays de résidence sera demandé.'
            ],
            [
                'id' => 'doc_phone_br',
                'label' => 'Numéro de téléphone mobile brésilien actif (+55)',
                'isMandatory' => false,
                'description' => 'Indispensable pour valider les codes SMS de sécurité et activer l\'application bancaire (Nubank, Inter, Itaú, Banco do Brasil).',
                'tip' => 'Achetez d\'abord votre puce prépayée Vivo, Claro ou TIM à l\'aide de votre CPF.'
            ],
            [
                'id' => 'doc_income_proof',
                'label' => 'Justificatif de revenus ou origine des fonds (Comprovante de Renda)',
                'isMandatory' => false,
                'description' => 'Contrat de travail CLT, bulletins de paie, bourse d\'études ou avis d\'imposition. Souvent demandé en banque traditionnelle et obligatoire pour les comptes non-résidents (CDE).',
                'tip' => 'En agence traditionnelle, vous pouvez demander le compte gratuit réglementé « Serviços Essenciais » sans frais mensuels.'
            ]
        ];

        $bankSteps = [
            [
                'number' => 1,
                'title' => 'Vérifier votre CPF et activer une ligne mobile brésilienne (+55)',
                'badge' => 'Prérequis obligatoire',
                'description' => "Avant toute demande bancaire, assurez-vous que votre CPF apparaît bien au statut « Regular » auprès de la Receita Federal et disposez d'un numéro de téléphone brésilien (+55) pour recevoir les codes de validation SMS.",
                'officialUrl' => 'https://servicos.receita.fazenda.gov.br/Servicos/CPF/ConsultaSituacao/ConsultaPublica.asp',
                'officialUrlLabel' => 'Vérifier mon statut CPF (gov.br)'
            ],
            [
                'number' => 2,
                'title' => 'Choisir la banque adaptée à votre statut (Digitale, Agence ou CDE)',
                'badge' => 'Règles Banco Central',
                'description' => "Résident avec CRNM : Nubank, Banco Inter ou C6 Bank offrent un compte 100% gratuit via application.\nNouvel arrivant sans CRNM physique : privilégiez un guichet Banco do Brasil, Itaú ou Santander.\nNon-résident à l'étranger : passez par une « Conta de Não Residente » (ex-CDE) chez BTG Pactual, Banco Rendimento ou pôles spécialisés.",
                'officialUrl' => 'https://www.bcb.gov.br/meubc/faqs/s/abertura-e-manutencao-de-conta',
                'officialUrlLabel' => 'Consulter les règles officielles (BCB)'
            ],
            [
                'number' => 3,
                'title' => 'Soumettre vos pièces et valider la vérification d\'identité (KYC)',
                'badge' => 'Dépôt du dossier',
                'description' => "Dans l'application digitale : scannez votre CRNM/RNM et réalisez la reconnaissance faciale (selfie).\nEn agence physique : apportez originaux + copies (CPF, passeport, CRNM ou protocole PF, justificatif d'adresse et revenus) et demandez explicitement le forfait gratuit « Serviços Essenciais ».",
                'officialUrl' => 'https://www.bb.com.br/site/pra-voce/contas/',
                'officialUrlLabel' => 'Voir les comptes Banco do Brasil'
            ],
            [
                'number' => 4,
                'title' => 'Activer votre clé Pix (Chave Pix) et recevoir votre carte',
                'badge' => 'Finalisation & Pix',
                'description' => "Dès l'ouverture validée (immédiat à 72h), enregistrez votre CPF, e-mail ou téléphone comme clé Pix dans l'application pour payer et recevoir de l'argent instantanément 24h/24 sans frais.",
                'officialUrl' => 'https://www.bcb.gov.br/estabilidadefinanceira/pix',
                'officialUrlLabel' => 'Découvrir le fonctionnement officiel Pix (BCB)'
            ]
        ];

        $bankFaqs = [
            [
                'question' => 'Puis-je ouvrir un compte bancaire au Brésil sans CPF ?',
                'answer' => "Non. Selon la réglementation fiscale et les règles de conformité de la Banco Central do Brasil, aucune banque (traditionnelle, digitale ou pour non-résident) n'est autorisée à ouvrir un compte sans numéro CPF actif."
            ],
            [
                'question' => 'Puis-je ouvrir un compte Nubank ou Banco Inter avec un visa touriste ?',
                'answer' => "En règle générale, non. Les banques digitales de détail exigent une résidence légale au Brésil (CRNM/RNM ou protocole officiel) et une adresse locale. Pour un court séjour ou sans résidence, vous devez passer par une compte global multi-devises (ex: Wise) ou ouvrir une Conta de Não Residente (CDE)."
            ],
            [
                'question' => 'Qu\'est-ce que le forfait gratuit « Serviços Essenciais » ?',
                'answer' => "La Résolution de la Banco Central garantit à tout résident le droit à un compte courant sans aucuns frais de tenue mensuelle (« Serviços Essenciais »), incluant carte de débit, retraits mensuels, extraits de compte et usage illimité de Pix."
            ],
            [
                'question' => 'Comment ouvrir un compte si je vis à l\'étranger (Non-Résident / CDE) ?',
                'answer' => "Si vous n'êtes pas résident fiscal au Brésil, la loi prévoit une « Conta de Não Residente » (anciennement CDE - Conta de Domiciliado no Exterior), proposée notamment par BTG Pactual, Banco Rendimento ou des guichets spécialisés de grandes banques avec passeport, CPF, preuve d'adresse à l'étranger et justificatif d'origine des fonds."
            ],
            [
                'question' => 'Que faire si une banque digitale refuse mon passeport ou protocole RNM ?',
                'answer' => "Les algorithmes des banques 100% mobiles bloquent parfois automatiquement les documents étrangers hors RG/CNH. Dans ce cas, présentez-vous en agence physique (Banco do Brasil, Itaú ou Caixa) ou sollicitez notre service d'assistance personnalisée pour constituer votre dossier bancaire."
            ]
        ];

        $bankPurposes = [
            [
                'id' => 'bank_daily_pix',
                'fr' => 'Ouvrir un compte courant pour salaire, loyer et paiements Pix',
                'pt' => 'Abrir conta corrente para salário, aluguel e Pix',
                'en' => 'Open a checking account for salary, rent, and Pix'
            ],
            [
                'id' => 'bank_digital_blocked',
                'fr' => 'Débloquer une ouverture refusée chez Nubank / Banco Inter / C6',
                'pt' => 'Resolver bloqueio/recusa em banco digital (Nubank, Inter, C6)',
                'en' => 'Resolve rejection at digital banks (Nubank, Inter, C6)'
            ],
            [
                'id' => 'bank_traditional_branch',
                'fr' => 'Préparer un dossier pour agence physique (Banco do Brasil, Itaú, Caixa)',
                'pt' => 'Preparar dossiê para agência (Banco do Brasil, Itaú, Caixa)',
                'en' => 'Prepare a branch application (Banco do Brasil, Itaú, Caixa)'
            ],
            [
                'id' => 'bank_non_resident_cde',
                'fr' => 'Ouvrir un compte de non-résident depuis l\'étranger (CDE)',
                'pt' => 'Abrir conta de não residente no exterior (CDE)',
                'en' => 'Open a non-resident account from abroad (CDE)'
            ],
            [
                'id' => 'other',
                'fr' => 'Autre besoin bancaire précis (à détailler ci-dessous)',
                'pt' => 'Outro objetivo bancário (detalhar abaixo)',
                'en' => 'Other specific banking goal (specify below)'
            ]
        ];

        $existingBankPrice = Demarche::where('step_number', 'ÉTAPE 02')->value('assistance_price');

        Demarche::updateOrCreate(
            ['step_number' => 'ÉTAPE 02'],
            [
                'priority' => 'Priorité 2',
                'icon' => 'landmark',
                'title' => 'Créer un compte bancaire',
                'subtitle' => 'Ouvrir votre compte courant ou digital au Brésil et activer Pix',
                'description' => 'Indispensable après votre CPF : recevez votre salaire (CLT), payez instantanément par Pix sans frais, encaissez ou effectuez des virements locaux et obtenez votre carte de débit brésilienne.',
                'delay' => 'Immédiat sur app (banque digitale) · 3 à 7j en agence',
                'cost' => '0 R$ (Banque digitale & Serviços Essenciais BCB)',
                'assistance_price' => $existingBankPrice !== null ? $existingBankPrice : 50.00,
                'button_text' => 'Voir le guide officiel',
                'guide_url' => 'https://www.bcb.gov.br/meubc/faqs/s/abertura-e-manutencao-de-conta',
                'official_source' => 'Banco Central do Brasil (Resolução CMN nº 4.753/2019)',
                'questions' => $bankQuestions,
                'checklist' => $bankChecklist,
                'steps' => $bankSteps,
                'faqs' => $bankFaqs,
                'assistance_purposes' => $bankPurposes,
                'status' => 'active',
                'order' => 2,
            ]
        );

        // 3. CARTEIRA DE TRABALHO (CTPS DIGITAL) - LIVRET DE TRAVAIL
        $ctpsQuestions = [
            [
                'id' => 'q_has_cpf',
                'question' => '1. Avez-vous déjà votre numéro de CPF régularisé ?',
                'subtitle' => 'La Carteira de Trabalho Digital est désormais entièrement indexée sur votre CPF.',
                'options' => [
                    [
                        'id' => 'cpf_yes',
                        'label' => 'Oui, je possède déjà mon numéro de CPF',
                        'detail' => 'Parfait. Votre numéro de CTPS sera exactement identique à votre numéro de CPF.'
                    ],
                    [
                        'id' => 'cpf_no',
                        'label' => 'Pas encore, je n\'ai pas de CPF',
                        'detail' => 'Important : Vous devez obligatoirement valider l\'Étape 01 (CPF) avant de créer votre CTPS.'
                    ]
                ]
            ],
            [
                'id' => 'q_govbr',
                'question' => '2. Avez-vous déjà créé votre compte d\'accès fédéral Gov.br ?',
                'subtitle' => 'Le compte Gov.br permet d\'accéder à tous les services publics brésiliens.',
                'options' => [
                    [
                        'id' => 'govbr_yes',
                        'label' => 'Oui, mon compte Gov.br est actif',
                        'detail' => 'Vous pouvez vous connecter directement sur l\'application mobile CTPS Digital.'
                    ],
                    [
                        'id' => 'govbr_no',
                        'label' => 'Non, je n\'ai pas encore de compte Gov.br',
                        'detail' => 'La création est immédiate et gratuite en ligne sur gov.br avec votre CPF.'
                    ]
                ]
            ],
            [
                'id' => 'q_contract',
                'question' => '3. Quel type d\'emploi visez-vous au Brésil ?',
                'subtitle' => 'Permet d\'identifier la réglementation applicable à votre future embauche.',
                'options' => [
                    [
                        'id' => 'clt_standard',
                        'label' => 'Contrat de travail salarié en entreprise (CLT standard)',
                        'detail' => 'Contrat classique : salaire mensuel, congés payés, 13ème mois et FGTS.'
                    ],
                    [
                        'id' => 'domestic_work',
                        'label' => 'Travail domestique déclaré (eSocial Doméstico)',
                        'detail' => 'Régime protecteur avec déclaration obligatoire de l\'employeur sur le portail eSocial.'
                    ],
                    [
                        'id' => 'internship_temp',
                        'label' => 'Stage professionnel ou contrat temporaire',
                        'detail' => 'Encadré par la convention de stage (Termo de Compromisso) et législation du travail.'
                    ]
                ]
            ]
        ];

        $ctpsChecklist = [
            [
                'id' => 'doc_cpf_active',
                'label' => 'Numéro de CPF valide et actif (Receita Federal)',
                'isMandatory' => true,
                'description' => 'Votre CPF sert d\'identifiant unique pour votre livret de travail numérique.',
                'tip' => 'Aucun livret physique n\'est nécessaire : votre CPF est votre numéro de livret.'
            ],
            [
                'id' => 'doc_id_migrant',
                'label' => 'Pièce d\'identité ou titre de séjour (RNM / CRNM / Protocole)',
                'isMandatory' => true,
                'description' => 'Document d\'identité en cours de validité prouvant votre situation légale sur le territoire.',
                'tip' => 'Le protocole de demandeur d\'asile (CONARE) autorise légalement l\'émission de la CTPS.'
            ],
            [
                'id' => 'doc_govbr_login',
                'label' => 'Identifiant et mot de passe de votre compte Gov.br',
                'isMandatory' => true,
                'description' => 'Nécessaire pour vous authentifier sur l\'application mobile ou le portail web.',
                'tip' => 'Un niveau de compte Bronze suffit pour consulter et activer votre CTPS.'
            ],
            [
                'id' => 'doc_app_download',
                'label' => 'Application officielle "Carteira de Trabalho Digital"',
                'isMandatory' => false,
                'description' => 'Disponible gratuitement sur Google Play Store (Android) et Apple App Store (iOS).',
                'tip' => 'Permet de suivre en direct vos déclarations de contrat, salaires et cotisations INSS.'
            ]
        ];

        $ctpsSteps = [
            [
                'number' => 1,
                'title' => 'Créer votre compte gratuit sur le portail Gov.br',
                'badge' => 'Étape préalable',
                'description' => 'Rendez-vous sur acesso.gov.br ou téléchargez l\'application Gov.br. Renseignez votre numéro de CPF et complétez les informations demandées pour définir votre mot de passe.',
                'action_label' => 'Accéder au portail Gov.br',
                'action_url' => 'https://acesso.gov.br'
            ],
            [
                'number' => 2,
                'title' => 'Télécharger l\'application "Carteira de Trabalho Digital"',
                'badge' => 'Application officielle',
                'description' => 'Installez l\'application officielle du Ministère du Travail sur votre smartphone Android ou iPhone.',
                'action_label' => 'Page officielle du Ministère',
                'action_url' => 'https://www.gov.br/pt-br/servicos/obter-a-carteira-de-trabalho'
            ],
            [
                'number' => 3,
                'title' => 'Activer votre livret en 1 clic avec votre CPF',
                'badge' => 'Activation immédiate',
                'description' => 'Connectez-vous avec votre compte Gov.br. Vos informations d\'état civil apparaissent automatiquement. Votre livret numérique est instantanément actif et prêt à l\'emploi.',
                'action_label' => 'Consulter en ligne',
                'action_url' => 'https://servicos.mte.gov.br'
            ],
            [
                'number' => 4,
                'title' => 'Transmettre votre CPF à votre futur employeur',
                'badge' => 'Embauche CLT',
                'description' => 'Lors de votre embauche, communiquez simplement votre numéro de CPF à l\'employeur. Il enregistrera votre contrat via le portail gouvernemental eSocial en moins de 48h.',
                'action_label' => null,
                'action_url' => null
            ]
        ];

        $ctpsFaqs = [
            [
                'question' => 'Un étranger sans titre définitif peut-il obtenir la CTPS ?',
                'answer' => 'Oui ! La législation brésilienne garantit le droit au travail pour les étrangers en situation régulière, y compris les demandeurs d\'asile munis d\'un protocole CONARE ou les titulaires de visas temporaires. Il suffit d\'avoir un CPF valide.'
            ],
            [
                'question' => 'Existe-t-il encore un livret de travail physique en papier bleu ?',
                'answer' => 'Non. Depuis la Loi nº 13.874/2019, la Carteira de Trabalho physique en carton bleu n\'est plus délivrée pour les nouveaux travailleurs. La version 100% numérique a exactement la même valeur légale et votre numéro de CPF remplace l\'ancien numéro de livret.'
            ],
            [
                'question' => 'Quels droits me garantit la CTPS déclarée (régime CLT) ?',
                'answer' => 'La déclaration CLT garantit : le salaire minimum légal ou conventionnel, le 13ème mois de salaire, 30 jours de congés payés avec prime de 1/3, le fonds de garantie FGTS (8% payés par l\'employeur) et la couverture sociale INSS (maladie, accident, retraite).'
            ],
            [
                'question' => 'Combien coûte l\'émission ou l\'utilisation de la CTPS Digital ?',
                'answer' => 'Elle est 100% gratuite. L\'application et l\'accès aux services du Ministère du Travail et de l\'Emploi ne font l\'objet d\'aucune taxe ni d\'aucun frais gouvernemental.'
            ]
        ];

        $ctpsPurposes = [
            [
                'id' => 'ctps_activate',
                'fr' => 'Activer ma Carteira de Trabalho Digital avec mon CPF',
                'pt' => 'Ativar minha Carteira de Trabalho Digital com meu CPF',
                'en' => 'Activate my Digital Work Card with my CPF'
            ],
            [
                'id' => 'govbr_account_help',
                'fr' => 'Créer ou débloquer mon compte Gov.br (Prata/Ouro)',
                'pt' => 'Criar ou desbloquear minha conta Gov.br (Prata/Ouro)',
                'en' => 'Create or unlock my Gov.br account (Prata/Ouro)'
            ],
            [
                'id' => 'clt_rights_check',
                'fr' => 'Vérifier mon contrat CLT et droits avec mon employeur',
                'pt' => 'Verificar contrato CLT e direitos trabalhistas',
                'en' => 'Verify CLT contract and labor rights with employer'
            ],
            [
                'id' => 'migrant_work_rights',
                'fr' => 'Débloquer mon droit au travail en tant qu\'étranger / réfugié',
                'pt' => 'Regularizar direito ao trabalho para migrante / refugiado',
                'en' => 'Regularize right to work as migrant / refugee'
            ],
            [
                'id' => 'other',
                'fr' => 'Autre demande liée au livret de travail ou contrat CLT',
                'pt' => 'Outra necessidade de trabalho ou contrat CLT',
                'en' => 'Other need related to work card or CLT contract'
            ]
        ];

        $existingCtpsPrice = Demarche::where('step_number', 'ÉTAPE 03')->value('assistance_price');

        Demarche::updateOrCreate(
            ['step_number' => 'ÉTAPE 03'],
            [
                'priority' => 'Priorité 3',
                'icon' => 'briefcase',
                'image_url' => '/images/demarche-ctps.jpg?v=2',
                'title' => 'Carteira de Trabalho (CTPS)',
                'subtitle' => 'Obtenir votre livret de travail officiel pour travailler légalement au Brésil',
                'description' => 'Le passeport indispensable pour tout emploi salarié au Brésil (contrat CLT). Désormais 100% numérique (CTPS Digital) via votre numéro CPF, elle garantit vos droits sociaux, congés payés, 13ème mois et cotisations retraite (INSS).',
                'delay' => 'Immédiat via l’application Carteira de Trabalho Digital',
                'cost' => '100% Gratuit (Service public fédéral)',
                'assistance_price' => $existingCtpsPrice !== null ? $existingCtpsPrice : 50.00,
                'button_text' => 'Voir le guide officiel',
                'guide_url' => 'https://www.gov.br/pt-br/servicos/obter-a-carteira-de-trabalho',
                'official_source' => 'Ministério do Trabalho e Emprego (Governo Federal)',
                'questions' => $ctpsQuestions,
                'checklist' => $ctpsChecklist,
                'steps' => $ctpsSteps,
                'faqs' => $ctpsFaqs,
                'assistance_purposes' => $ctpsPurposes,
                'status' => 'active',
                'order' => 3,
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
