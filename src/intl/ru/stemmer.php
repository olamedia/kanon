<?php
class ruStemmer{
    protected $_cacheEnabled = false;
    protected $_cache = array(
	'авось' => 'ав',
	'батальонных' => 'батальон',
	'военных' => 'воен',
	'воздусех' => 'воздусех',
	'воздух' => 'воздух',
	'доспех' => 'доспех',
	'евнух' => 'евнух',
	'олух' => 'олух',
	'орех' => 'орех',
	'пастух' => 'пастух',
	'петух' => 'петух',
	'потух' => 'потух',
	'успех' => 'успех',

	'бывшему' => 'бывш',
	'вытаращены' => 'вытаращ',
	'глядевшему' => 'глядевш',
	'длинных' => 'длин',
	'деревянных' => 'деревя',
	'дрянных' => 'дрян',
	'заплеванному' => 'заплева',
	'защити' => 'защит',
	'знающему' => 'знающ',
	'идущему' => 'идущ',
	'имевшему' => 'имевш',
	'имеющему' => 'имеющ',
	'неожиданному' => 'неожида',
	'нескончаемому' => 'несконча',
	'неслыханному' => 'неслыха',
	'нищему' => 'нищ',
	'обращены' => 'обращ',
	'общему' => 'общ',
	'однех' => 'однех',
	'отчаянному' => 'отчая',
	'подвернувшемуся' => 'подвернувш',
	'потащили' => 'потащ',
	'революционных' => 'революцион',
	'сенных' => 'сен',
	'сидевшему' => 'сидевш',
	'сокращены' => 'сокращ',
	'сонных' => 'сон',
	'старух' => 'старух',
	'стенных' => 'стен',
	'странных' => 'стран',
	'уважаемому' => 'уважа',
	'устроенных' => 'устроен',
	'хотевшему' => 'хотевш',
	'четырех' => 'четырех',
	'чинных' => 'чин',
	'будущему' => 'будущ',
	
	);
	protected $_type = array();
    const VOWEL = '/аеиоуыэюя/u';
    const PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/u';
    const REFLEXIVE = '/(с[яь])$/u';
	const ADJECTIVE_IT = '/([цкнгшщзхфвпрлджчсмтб][иы]т)[ао]$/u';//ствдрослжнмч
	const ADJECTIVE_NN = '/([цкнгшщзхфвпрлджчсмтб][оияе]н)н[иы]х$/u';//ствдрослжнмч
	// FIXME ящему
    const ADJECTIVE = '/([еиыо]е|[иы]ми|или|([у]?[ю])?щему|[еиыо][йм]|[ео]го|еых|[уюое]ю|[ая]я|ены|(нн)?[иы]х)$/u';
    const PARTICIPLE = '/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/u';
    const VERB = '/((о|ена|ейте|уйте|ите|или|[ыи][тл][оаи]?|ят|ей|уй|им|ым|ен[оы]|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/u';
    const NOUN = '/(а|[ео]в|[иь]е|е|иями|ы?вшему|[ая]м|ы|ит|[ое]му|ен|[ео]ч?к|к|у[ею]т|ями|ами|еи|ии|и|ией|ей|ой|у|ий|й|и|ы|ь|ию|ью|ю|ия|ья|я)х?$/u';
    const RVRE = '/^(.*?[аеиоуыэюя])(.*)$/u';
    const DERIVATIONAL = '/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/u';
    protected static $_instance = null;
    protected function s(&$s, $re, $to){
        $orig = $s;
        $s = preg_replace($re, $to, $s);
        return $orig !== $s;
    }
	public function getType(){
		return implode(' ', $this->_type);
	}
    protected function m($s, $re){
        return preg_match($re, $s);
    }
    /**
     * 
     * @return ruStemmer
     */
    public function getInstance(){
    	if (self::$_instance === null){
    		self::$_instance = new self();
    	}
    	return self::$_instance;
    }
    public static function stem($word){
    	return self::getInstance()->stemWord($word);
    }
    public function stemWord($word){
		$this->_type = array();
        $word = mb_strtolower($word, 'UTF-8');
        $word = preg_replace('/ё/u', 'е',$word);
        # Check against cache of stemmed words
        if (isset($this->_cache[$word])) {//$this->_cacheEnabled && 
            return $this->_cache[$word];
        }
        $stem = $word;
        do {
          if (!preg_match(self::RVRE, $word, $p)) break;
          $start = $p[1];
          $RV = $p[2];
          if (!$RV) break;
          # Step 1
          if (!$this->s($RV, self::PERFECTIVEGROUND, '')) {
			$this->_type[] = 'REFLEXIVE';
              $this->s($RV, self::REFLEXIVE, '');
              if ($this->m($RV, self::ADJECTIVE_IT)){
				$this->s($RV, self::ADJECTIVE_IT, '\1');
				$this->_type[] = 'ADJECTIVE_IT';
                $this->s($RV, self::PARTICIPLE, '');
              }elseif ($this->m($RV, self::ADJECTIVE_NN)){
				$this->s($RV, self::ADJECTIVE_NN, '\1');
				$this->_type[] = 'ADJECTIVE_NN';
                $this->s($RV, self::PARTICIPLE, '');
			  }elseif($this->s($RV, self::ADJECTIVE, '')) {
				$this->_type[] = 'ADJECTIVE';
                $this->s($RV, self::PARTICIPLE, '');
              }else{
                  if (!$this->s($RV, self::VERB, '')){
						$this->_type[] = 'NOUN';
                      $this->s($RV, self::NOUN, '');
					 }else{
						$this->_type[] = 'VERB';
					 }
              }
          }
          # Step 2
          $this->s($RV, '/и$/u', '');
          # Step 3
          if ($this->m($RV, self::DERIVATIONAL)){
			  $this->_type[] = 'DERIVATIONAL';
              $this->s($RV, '/ость?$/u', '');
		  }
          # Step 4
          if (!$this->s($RV, '/ь$/u', '')) {
              $this->s($RV, '/ейше?/u', '');
              $this->s($RV, '/нн$/u', 'н');
          }
		  // суффиксы к/ок/ек (поряд)к[и] (поряд)ок (ком)ок (ком)к[и] (кош)ек (кош)к[и]
		  // цкнгшщзхфвпрлджчсмтб
          //$this->s($RV, '/([цкнгшщзхфвпрлджчсмтб])[ое]?к$/u', '\1');
          //$this->s($RV, '/([цкгшщхфвпрлджчсмтб])[аио]?м$/u', '\1');
          //$this->s($RV, '/([нскцрвл])([уыоа]|[и]я)[х]?$/u', '\1');
          //$this->s($RV, '/([м])[ии]$/u', '\1');
          $stem = $start.$RV;
        } while(false);
        if ($this->_cacheEnabled) $this->_cache[$word] = $stem;
        return $stem;
    }
    public function clearCache(){
        $this->_cache = array();
    }
}