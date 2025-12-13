<?php
declare(strict_types=1);

namespace Hronic\Components;

use function Thorm\{state,el,text,on,read,concat,addTo,set,attrs,num,ev,cls,repeat, item};
use Thorm\IR\Node\Node;

class Content 
{
    public function __construct(){}

    public static function get(): Node
    {
        $paragraph = el('p', [
            cls('cis-4lh clamp-4'),
        ], [
            text(item('text')),
        ]);

        $paragraphs = state([
            ['id'=>1,'text'=>'Într-o lume plină de posibilități nelimitate și de descoperiri fascinante, literatura de science fiction îți oferă cheia către universuri neexplorate și călătorii extraordinare. Este o invitație la a călători în timp, în spațiu și în mintea umană, să te pierzi în labirintul imaginației și să descoperi inovații tehnologice care îți vor lărgi orizonturile.',],
            ['id'=>2,'text' => 'Fiecare pagină este o poartă către aventuri incredibile, în care poți întâlni ființe extraterestre sau inteligențe artificiale, să explorezi galaxii îndepărtate sau să pătrunzi în adâncurile minții umane. În lumea science fiction, nu există limite, iar doar imaginația ta este singurul punct de plecare.'],
            ['id'=>3,'text'=>'Citind literatură de science fiction, nu doar că te distrezi, ci și îți dezvolți gândirea critică și abilitatea de a anticipa viitorul. Fiecare poveste este o oglindă a societății noastre contemporane sau o previziune a direcției în care ne îndreptăm. Așadar, fiecare carte pe care o deschizi îți oferă oportunitatea de a reflecta asupra lumii în care trăiești și de a înțelege mai bine propria ta umanitate.'],
            ['id'=>4,'text'=>'Nu există moment mai bun decât acum să te lansezi în această călătorie epică. Într-o lume în continuă schimbare, cu provocări și mistere în fiecare colț, literatura de science fiction este farul tău în întuneric, ghidul tău către aventuri neînchipuite și călătorii către noi orizonturi. Așadar, ridică-ți cartea și pregătește-te pentru a călători în universuri infinite și pentru a-ți alimenta imaginația într-un mod care nu cunoaște limite!'],
        ]);

        $articles = state([
            [
                'id'=>101,
                'title'=>'Venus, o atracție irezistibilă pentru science fiction',
                'excerpt'=> 'Venus, cel mai strălucitor astru de pe cerul nopții după Lună, își trage numele de la zeița romană a frumuseții și a dragostei. Lumina sa este atât de puternică încât, în nopțile fără Lună, poate să arunce umbre singură, iar câteodată poate fi observată cu ochiul liber în plină zi. Tot de la romani provine și numele pe care strămoșii noștri l-au dat planetei: Luceafărul.',
                'category'=>'science fiction',
                'link'=>'/pove%C8%99ti/sf/venus-atractie-irezistibila-pentru-science-fiction',
                'publish_date'=>'10-14-2020', 
                'author'=>'Marius Bucur', 
                'comments'=>12, 
                'thumb'=>'images/media/venus_alien_landscape.webp'
            ],
            [
                'id'=>102,
                'title'=>'Comoara de la capătul curcubeului',
                'excerpt' => 'Yagla, un tânără curajoasă, găsește comoara de la capătul curcubeului, un loc plin de bogății și artefacte magice. Grehind, un personaj malefic, caută si el comoara și și-o însușește. Întoarsă în orașul natal, Grehind folosește artefactele pentru a provoca haos și dezastre, distrugând clădiri și recolte. Determinat să salveze orașul, Yagla trebuie să recupereze comoara și să oprească distrugerea provocată de Grehind.',
                'category'=>'fantasy',
                'link'=>'/pove%C8%99ti/fantasy/comoara-de-la-capatul-curcubeului',
                'publish_date'=>'10-14-2020', 
                'author'=>'Marius Bucur', 
                'comments'=>858, 
                'thumb'=>'images/media/comoara-de-la-capatul-curcubeului-300x300.jpg'
            ],
            [
                'id'=>103,
                'title'=>'Refugiul Vrăjitoarei',
                'excerpt'=>'"Casa de pe Dealul Întunecat" urmărește povestea unui grup de prieteni care, curioși și dornici de aventură, explorează o casă gotică bântuită. Legendele spun că în această casă a trăit o vrăjitoare trădată de cei pe care îi ajutase, iar spiritele lor neliniștite încă păzesc locul. Pe măsură ce prietenii descoperă secretele întunecate ale casei, ei se confruntă cu prezențe malefice și pericole supranaturale, iar ceea ce începe ca o explorare devine o luptă disperată pentru supraviețuire. Povestea este un amestec captivant de mister, groază și elemente gotice, ținând cititorii în suspans până la ultima pagină.',
                'category'=>'horror',
                'link'=>'/pove%C8%99ti/sf/casa-de-pe-dealul-intunecat-terorile-vrajitoarei',
                'publish_date'=>'10-14-2020', 
                'author'=>'Marius Bucur', 
                'comments'=>33, 
                'thumb'=>'images/media/casa-bantuita-dealul-intunecat-halloween-mister-300x300.webp'
            ],
            [
                'id'=>10,
                'title'=>'o noapte liniștită',
                'excerpt'=>'"O tranziție între o noapte inițial liniștită și calmă și transformarea ei într-un coșmar plin de temeri și lacrimi.',
                'category'=>'poezie',
                'link'=>'/pove%C8%99ti/sf/aceasta-e-o-noapte-linistita',
                'publish_date'=>'10-14-2020', 
                'author'=>'Marius Bucur', 
                'comments'=>23, 
                'thumb'=>'images/media/aceasta-e-o-noapte-linistita-300x300.webp'
            ],
            
        ]);

        $card = el('div', [cls('col-12 col-sm-6 col-lg-3 mb-2'),], [
            el('div', [
                cls('card border-0'),
                //attrs(['style'=>'min-height: 33em;max-height: 33em;overflow: hidden;']),
            ], [
                el('h1', [ 
                    cls('fs-5 meta-category position-absolute fw-bold text-light p-2 rounded')
                ], [
                    text(item('category')),
                ] ),
                el('a', [
                    cls('img-thumbnail'),
                    attrs([ 'href'=> item('link') ]),
                ], [
                    el('div', [cls('img-container')], [
                        el('img', [
                            cls(''), 
                            attrs(
                                [
                                    'src'=>item('thumb'),
                                    'alt'=>item('title'),
                                    'loading'=>'lazy',
                                    'decoding'=>'async',
                                ])
                        ]),
                    ]),
                ]),
                el('div', [
                    cls('card-body p-2'),
                ], [
                    el('div', [
                    cls('d-flex align-items-center'),
                ], [
                    el('div', [
                            cls('meta d-flex align-items-center'),
                        ], [
                            el('div', [
                                    cls('material-symbols-outlined meta-icon'),
                                ], 
                                [ text('schedule') ]
                            ),
                            el('div', [ 
                            ], [ text(item('publish_date')) ]),
                        ]),
                        el('div', [ 
                            cls('meta d-flex align-items-center'),
                        ], [
                            el('span', [ cls('material-symbols-outlined meta-icon') ], [ text('ink_pen') ]),
                            el('span', [], [ text(item('author')) ]),
                        ]),
                        el('div', [ 
                            cls('meta d-flex align-items-center'),
                        ], [
                            el('span', [ cls('material-symbols-outlined meta-icon') ], [ text('forum') ]),
                            el('span', [], [ text(item('comments')) ]),
                        ]),
                    ]),
                    el('h2', [ cls('fs-6 fw-bolder mt-1 article-box-title col-12')], [ text( item('title') ) ]),
                    el('p', [ cls('font-weight-normal font-size-9-10 article-box-excerpt') ],  [ text( item('excerpt') ) ]),
                ]),
                
            ])
        ]);

        $component = el('div', [
            cls('container content'),
            //attrs(['style' => 'margin-top:120px;']),
        ], [
            el('div', [
                cls('row'),
                attrs([ 'id' => 'featured-articles' ]),
            ], [
                repeat(
                    read($articles),
                    item('id'),
                    $card
                ),
            ]),
            repeat(read($paragraphs), item('id'), $paragraph),
        ]);

        return $component;
    }
}
