<?php
/**
 * пример скрипта поздравления всех друзей-девушек с 8 марта
 */
ini_set('error_reporting', E_ALL);
ini_set ('display_errors', 1);

include(dirname(__FILE__).'/vk.php');

$vk = new vk(123456789,'mypass','+74991234567');

$MESSAGE_I = 0;

$vk_users = @file_get_contents('friends.txt');

if($vk_users){
	$str = explode("\n", $vk_users);
	if($str){
		foreach($str as $v){
			
			if($v){
				$split = explode('~SPLIT~', $v);
				if($split){
					
					$id = (int)$split['0'];
					$name = $split['1'];
					$sex = (int)$split['2'];
					$name_short = trim($split['5']);
					
					if($id && $name && $sex){
						
						if($sex===2){
							
							//-------------------------------------------------------
							
							$MESSAGE_FILE = $_SERVER['DOCUMENT_ROOT'].'/8marta/'.$id;
							
							if(is_file($MESSAGE_FILE) || $MESSAGE_I>2){ continue; }
							
							$vk->file($MESSAGE_FILE);
							
							$MESSAGE_I++;
							
							//-------------------------------------------------------
							
							$rand = rand(0,11);
							
							$message = 'В восьмое марта я тебе желаю,
Хорошую машину в гараже,
Тебя ~name_short~ я очень уважаю –
Ты будешь ездить только на Порше.
И чтобы всё смогла, чего хотела,
И голова чтоб не болела.
Чтоб с каждым годом все моложе.
И шелковистей чтобы кожа.
Чтоб понимали и любили,
И на руках всегда носили.
Весна приносит обновленье,
Улыбки, счастье и цветы.
И я хочу, чтоб в день весны рожденье
Твои исполнились мечты!
С праздником весны ~name_short~! :)';
							
							$vk->sendTo($id);
							
							$media = '&attach1=18656258_324061972&attach1_type=photo&attach2=-32080625_199993032&attach2_type=audio';
							
							$r = $vk->message(str_replace('~name_short~', $name_short, $message), $media);
							
							if( !empty($r) ){
								$vk->file($MESSAGE_FILE,$r);
							}
						}else{
							
						}
					}
				}
			}
		}
	}
}
echo '8marta '.time();