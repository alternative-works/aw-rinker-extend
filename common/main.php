<?php

//Rinker画像サイズ一括設定
function yyi_rinker_default_image_size( $s ) {
	return 'L';
}
add_action( 'yyi_rinker_default_image_size', 'yyi_rinker_default_image_size' );


// Rinkerよりあとに実行してログインユーザーに対してアフィタグを取り除く
function replace_aatag($content)
{
	if (is_user_logged_in()) {
		$search = '/amazon.co.jp\/[\s\S,%,,亜-熙,ぁ-ん,ァ-ヶ].*\/dp/';
		$replace = 'amazon.co.jp/dp';
		$content = preg_replace($search, $replace, $content);
		$search = '/(dp\/[a-z,A-Z,0-9].*?)\?[\s\S]*?"/';
		$replace = '${1}"';
		return preg_replace($search, $replace, $content);
	}

	return $content;
}
add_filter('the_content', 'replace_aatag', 990);



function rinker_extend($atts,$content=null){
	extract(shortcode_atts(array(
        'post_id' => "",
        'ribbon_type' => "",
	), $atts));
	$id=$post_id;
	$tag="careru00-22";
	//Rinkerショートコード
	$link=new stdClass;
	{
		$rinker=do_shortcode("[itemlink post_id=\"{$id}\"]");
		$dom = new DOMDocument();
	//空pタグWarning非表示
error_reporting(0);
	$dom->loadHTML( mb_convert_encoding($rinker, 'HTML-ENTITIES', 'UTF-8'));
error_reporting(-1);
		$title=$dom->getElementsByTagName('a')->item(1)->nodeValue;

	//yyi-rinker-title
	//Kindleチェック
	if($dom->getElementsByTagName('a')->item(3)->nodeValue=='Kindle'){
		$link->kindle=$dom->getElementsByTagName('a')->item(3)->getAttribute('href');	
		$offset=1;
	}else{
	$link->kindle='';
		$offset=0;
	}
		$link->amazon=$dom->getElementsByTagName('a')->item(0+$offset)->getAttribute('href');
		//rakutenlink
		$link->rakuten=$dom->getElementsByTagName('a')->item(4+$offset)->getAttribute('href');
		$link->rakutentrack=$dom->getElementsByTagName('img')->item(1)->getAttribute('src');
		//rakutenlink
		$link->yahoo=$dom->getElementsByTagName('a')->item(5+$offset)->getAttribute('href');
		$link->yahootrack=$dom->getElementsByTagName('img')->item(2)->getAttribute('src');

		if($link->kindle!=''){
			$kindle = <<<EOL
<li class="amazonkindlelink">
						<a rel="nofollow noopener external" href="{$link->kindle}" class="yyi-rinker-link yyi-rinker-tracking" data-click-tracking="amazon_kindle {$id} {$title}" data-vars-amp-click-id="amazon_kindle {$id} {$title}">Kindle</a>					</li>			
EOL;
		}else{
			$kindle='';
		}
		
		if($dom->getElementsByTagName('a')->length>(6+$offset)){
			$link->free=$dom->getElementsByTagName('a')->item(6+$offset)->getAttribute('href');
			$mes=$dom->getElementsByTagName('a')->item(6+$offset)->nodeValue;

			$free4 = <<<EOL
<li class="freelink4">
<a rel="nofollow noopener external" href="{$link->free}" class="yyi-rinker-link yyi-rinker-tracking" data-click-tracking="free_4 {$id} {$title}" data-vars-amp-click-id="free_4 {$id} {$title}">cc</a>
</li>
EOL;
		}else{
			$free4='';
		}
	
//$link['amazon']=$dom->getElementsByTagName('a')->item(0)->getAttribute('href');
		$img=$dom->getElementsByTagName('img')->item(0)->getAttribute('src');
		$size = new stdClass;
		$size->width=$dom->getElementsByTagName('img')->item(0)->getAttribute('width');
		$size->height=$dom->getElementsByTagName('img')->item(0)->getAttribute('height');
		//nodeValue
		$button = new stdClass;
		$button->amazon="Amazon";
		$button->rakuten="楽天市場";
		$button->yahoo="Yahoo!ショッピング";

	}
	preg_match('/dp\/([A-Za-z0-9].*)\?/',$link->amazon,$matches);
	$dpid=$matches[1];

	if(is_user_logged_in()){
		$tag='';
	}else{
		$tag="&tag={$tag}&linkCode=ogi";
	}

	//コンテンツ解析
	{
			$dom = new DOMDocument();
	//空pタグWarning非表示
error_reporting(0);
	$dom->loadHTML( mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
error_reporting(-1);
		//テーブルは評価表専用
		$trs=$dom->getElementsByTagName('tr');

		$bk='';
		$len=strlen($title);



		if($trs->length>0){
			$volume='<table class="no-border-table" style="border-collapse: collapse; width: 100%;"><tbody>';
		}else{
			$volume = '';
		}
		foreach($trs as $tr){

			$td=$tr->getElementsByTagName('td');

			$t_label=str_replace(array('[',']'),array('&#91;','&#93;'),$td->item(0)->nodeValue);
			$t_score=str_replace(array('[',']'),array('&#91;','&#93;'),$td->item(1)->nodeValue);
			if(is_numeric($t_score)){
				$t_score=do_shortcode("[star rate=\"{$t_score}\" number=0]");
			}else{
			}
			$volume .= <<<EOL
<tr>
	<td>{$t_label}</td>
	<td>{$t_score}</td>
</tr>
EOL;
		}
		if($trs->length>0){
		$volume.='</tbody></table>';
		}	


		$ps=$dom->getElementsByTagName('p');
		foreach($ps as $p){
			$t_content = $dom->saveXML($p);
			$volume.=$t_content;
		}
	
	}
	
	//リボン処理
	if($ribbon_type!=''){
		
		

		//<span class="ribbon17">NEW</span>
		/*
			new
			pickup
			check
			detail
			recommend
		*/
		$ribbonValue='';
		switch($ribbon_type){
			case 'new':
				$ribbonValue='<span class="rinker-ribbon-new">NEW</span>';
				break;
			case 'pickup':
				$ribbonValue='<span class="rinker-ribbon-pickup">ピックアップ</span>';
				break;
			case 'check':
				$ribbonValue='<span class="rinker-ribbon-check">チェック</span>';
				break;
			case 'detail':
				$ribbonValue='<span class="rinker-ribbon-detail">詳細</span>';
				break;
			case 'recommend':
				$ribbonValue='<span class="rinker-ribbon-recommend">オススメ</span>';
				break;
			default;
		}
		
		if($ribbonValue==''){
			$ribbon_html='';
		}else{
			$ribbon_html='<div class="rinker-ribbon-wrap">';
			$ribbon_html.=$ribbonValue;
			$ribbon_html.='</div>';
		}


		
	}else{
		$ribbon_html='';
	}
	
	
	$html = <<<EOL

<div id="rinkerid{$id}" class="yyi-rinker-contents yyi-rinker-postid-{$id} yyi-rinker-img-l yyi-rinker-catid-1 ">
{$ribbon_html}
	<div class="yyi-rinker-box">
		<div class="yyi-rinker-image">
							<a rel="nofollow noopener external" href="{$link->amazon}" class="yyi-rinker-tracking" data-click-tracking="amazon_img {$id} {$title}" data-vars-click-id="amazon_img {$id} {$title}"><img alt="" src="{$img}" width="{$size->width}" height="{$size->height}" class="yyi-rinker-main-img" style="border: none;"></a>					</div>
		<div class="yyi-rinker-info">
			<div class="yyi-rinker-title">
									<a rel="nofollow noopener external" href="{$link->amazon}" class="yyi-rinker-tracking" data-click-tracking="amazon_title {$id} {$title}" data-vars-amp-click-id="amazon_title {$id} {$title}">{$title}</a>							</div>
			<div class="yyi-rinker-description">{$volume}</div>
			<div class="yyi-rinker-review"><a href="https://www.amazon.co.jp/product-reviews/{$dpid}/?filterByStar=positive{$tag}">Amazonでカスタマーレビューを見る</a></div>
			<ul class="yyi-rinker-links">
			{$kindle}
																					<li class="amazonlink">
						<a rel="nofollow noopener external" href="{$link->amazon}" class="yyi-rinker-link yyi-rinker-tracking"  data-click-tracking="amazon {$id} {$title}" data-vars-amp-click-id="amazon {$id} {$title}">{$button->amazon}</a>					</li>
													<li class="rakutenlink">
						<a rel="nofollow noopener external" href="{$link->rakuten}" class="yyi-rinker-link yyi-rinker-tracking" data-click-tracking="rakuten {$id} {$title}" data-vars-amp-click-id="rakuten {$id} {$title}">{$button->rakuten}</a><img alt="" src="{$link->rakutentrack}" width="1" height="1" style="border:none;">					</li>
													<li class="yahoolink">
						<a rel="nofollow noopener external" href="{$link->yahoo}" class="yyi-rinker-link yyi-rinker-tracking"   data-click-tracking="yahoo {$id} {$title}" data-vars-amp-click-id="yahoo {$id} {$title}">{$button->yahoo}</a><img alt="" src="{$link->yahootrack}" width="1" height="1" style="border:none;">					</li>
						{$free4}
															</ul>
		</div>
	</div>
</div>

EOL;

	return $html;
}

add_shortcode('itemlinkd', 'rinker_extend');