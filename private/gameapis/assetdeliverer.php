<?php
	use anorrl\Asset;
	use anorrl\Place;
	use anorrl\enums\AssetType;
	use anorrl\utilities\ByteReader;

	if(!isset($_GET['id']) && !isset($_GET['ID']) && !isset($_GET['Id'])) {
		die(http_response_code(500));
	}

	if(isset($_GET['id'])) {
		$id = intval($_GET["id"]);
	} else if(isset($_GET['ID'])) {
		$id = intval($_GET["ID"]);
	} else if(isset($_GET['Id'])) {
		$id = intval($_GET["Id"]);
	}

	function checkMimeType($contents) {
		$file_info = new finfo(FILEINFO_MIME_TYPE);
		return $file_info->buffer($contents);
	}

	$access = CONFIG->asset->key;
	$domain = CONFIG->domain;
	
	$user = SESSION ? SESSION->user : null;

	$asset = Asset::FromID($id);
	if($asset != null) {
		$version = isset($_GET['version']) ? intval($_GET['version']) : -1;
		$contents = $asset->getFileContents($version);

		if($contents != null) {
			if($asset->type == AssetType::PLACE) {
				$place = Place::FromID($asset->id);
				
				if($place->copylocked) {
					$error = false;
					if($user == null && !isset($_GET['access'])) {
						$error = true;
					} 
					
					if(!$error && (isset($_GET['access']) && trim($_GET['access']) != $access)) {
						$error = true;
					}

					if(!$error && $user != null && $place->creator->id != $user->id && !$user->isAdmin()) {
						$error = true;
					}

					if($error) {
						if(!($_SERVER['HTTP_USER_AGENT'] == "Roblox/WinInet" || $_SERVER['HTTP_USER_AGENT'] == "Roblox/WinHttp"))
							die(http_response_code(503));
					}
				}
			} else{
				if (isset($_GET['serverplaceid'])) {
					$serverplace = Place::FromID(intval($_GET['serverplaceid']));
					
					if ($serverplace == null && intval($_GET['serverplaceid']) != 0) {
						http_response_code(400);
						die("Bad Request");
					}

					if(!$serverplace->gears_enabled && $asset->type == AssetType::GEAR && intval($_GET['serverplaceid']) != 0) {
						die(file_get_contents($_SERVER['DOCUMENT_ROOT']."/private/templates/assets/nothing.rbxm"));
					}
					
					$blacklist = ["MeshId", "Script", "Remote", "Service", "Model"];
					$whitelist = ["Keyframe", "Animation"];
					
					/*foreach($whitelist as $white) {
						if(strpos($contents, $white) !== false) {
							foreach($blacklist as $black) {
								if(strpos($contents, $black) !== false && (intval($_GET['serverplaceid']) != 0 && $asset->type != AssetType::HAT && $asset->type != AssetType::MODEL && !(intval($_GET['serverplaceid']) == 0 && $asset->type == AssetType::GEAR))) { // hope that model whitelist aint gonna bite my ass
									http_response_code(405);
									die("Method Not Allowed");
								}
							}
						}
					}*/
				}
			}

			header("Content-Type: application/octet-stream");
			Header('Content-Disposition: attachment; filename="'.$id.'"');
			die($contents);
			
		} else {
			http_response_code(404);
			die("Asset not found!");
		}
	} else {
		$roblosec = CONFIG->asset->roblosec;
		if(CONFIG->asset->canforward && strlen(trim($roblosec)) != 0) {
			

			if(isset($_GET['version'])) {
				$version = intval($_GET['version']);
			}

			if(!file_exists($_SERVER['DOCUMENT_ROOT']."/../assets/rbx_".$id.(isset($_GET['version']) ?  "_".$version : ""))) {
				$url = "https://assetdelivery.roblox.com/v1/asset/?id=".$id.(isset($_GET['version']) ? '&version='.$version : "");
				$ch = curl_init ($url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ["Cookie: .ROBLOSECURITY=$roblosec"]);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				$output = curl_exec($ch);
				curl_close($ch);
				
				$mimetype = checkMimeType($output);
				
				if($mimetype == "application/gzip") {
					$output = gzdecode($output);
					$mimetype = checkMimeType($contents);
				}
				
				if(str_contains($mimetype, "json")) {
					$contents = "";

					if(!isset($_GET['version'])) {
						file_put_contents($_SERVER['DOCUMENT_ROOT']."/../assets/rbx_".$id, $contents);
					} else {
						file_put_contents($_SERVER['DOCUMENT_ROOT']."/../assets/rbx_".$id."_".$version, $contents);
					}

					echo "Unauthorised access to this roblox asset!";
					die(http_response_code(500));
				} else {
					header("Content-Type: $mimetype");

					$contents = str_replace("www.roblox.com", $domain, $output);

					$reader = new ByteReader();
					$reader->buffer = $contents;
					if (($reader->String(8)) == "version ") { // "Invalid mesh file"
						$version = ($reader->String(4));
						switch($version) {
							case "3.00":
							case "3.01":
							case "4.00":
							case "4.01":
							case "5.00":
								$newline = $reader->Byte();
								if ($newline == 0x0A | ($newline == 0x0D && $reader->Byte() == 0x0A)) { // "Bad newline"
									$begin = $reader->GetIndex();
									$headerSize = 0;
									$vertexSize = 0;
									$faceSize = 12;
									$lodSize = 4;
									$nameTableSize = 0;
									$facsDataSize = 0;
									$lodCount = 0;
									$vertexCount = 0;
									$faceCount = 0;
									$boneCount = 0;
									$subsetCount = 0;
									switch (substr($version, 0, 2)) {
										case "3.":
											$headerSize = $reader->UInt16LE();
											if ($headerSize >= 16) { // "Invalid header size"
												$vertexSize = $reader->Byte();
												$faceSize = $reader->Byte();
												$lodSize = $reader->UInt16LE();
												$lodCount = $reader->UInt16LE();
												$vertexCount = $reader->UInt32LE();
												$faceCount = $reader->UInt32LE();
											}
											break;
										case "4.":
											$headerSize = $reader->UInt16LE();
											if ($headerSize >= 24) { // "Invalid header size"
												$reader->Jump(2); // uint16 lodType;
												$vertexCount = $reader->UInt32LE();
												$faceCount = $reader->UInt32LE();
												$lodCount = $reader->UInt16LE();
												$boneCount = $reader->UInt16LE();
												$nameTableSize = $reader->UInt32LE();
												$subsetCount = $reader->UInt16LE();
												$reader->Jump(2); // byte numHighQualityLODs, unused;
												$vertexSize = 40;
											}
											break;
										case "5.":
											$headerSize = reader->UInt16LE();
											if ($headerSize >= 32) { // "Invalid header size"
												$reader->Jump(2); // uint16 meshCount;
												$vertexCount = $reader->UInt32LE();
												$faceCount = $reader->UInt32LE();
												$lodCount = $reader->UInt16LE();
												$boneCount = $reader->UInt16LE();
												$nameTableSize = $reader->UInt32LE();
												$subsetCount = $reader->UInt16LE();
												$reader->Jump(2); // byte numHighQualityLODs, unused;
												$reader->Jump(4); // uint32 facsDataFormat;
												$facsDataSize = $reader->UInt32LE();
												$vertexSize = 40;
											}
											break;
									}
									$reader->SetIndex($begin + $headerSize);
									if ($vertexSize >= 36 && $faceSize >= 12 & $lodSize >= 4) { // "Invalid vertex size", "Invalid face size", "Invalid lod size"
										$fileEnd = $reader->GetIndex()
											+ ($vertexCount * $vertexSize)
											+ ($boneCount > 0 ? $vertexCount * 8 : 0)
											+ ($faceCount * $faceSize)
											+ ($lodCount * $lodSize)
											+ ($boneCount * 60)
											+ ($nameTableSize)
											+ ($subsetCount * 72)
											+ ($facsDataSize);
										if ($fileEnd == $reader->GetLength()) { // "Invalid file size"
											$faces = array();
											$vertices = array();
											$normals = array();
											$uvs = array();
											$tangents = array();
											$enableVertexColors = $vertexSize >= 40;
											$vertexColors = array();
											$lods = array(0, $faceCount);
											for($i = 0; $i < $vertexCount; $i++) { // Vertex[vertexCount]
												$vertices[$i * 3] = $reader->FloatLE();
												$vertices[$i * 3 + 1] = $reader->FloatLE();
												$vertices[$i * 3 + 2] = $reader->FloatLE();
												$normals[$i * 3] = $reader->FloatLE();
												$normals[$i * 3 + 1] = $reader->FloatLE();
												$normals[$i * 3 + 2] = $reader->FloatLE();
												$uvs[$i * 2] = $reader->FloatLE();
												$uvs[$i * 2 + 1] = 1 - $reader->FloatLE();
												$tangents[$i * 4] = $reader->Byte() / 127 - 1; // tangents are mapped from [0, 254] to [-1, 1]; byte tx, ty, tz, ts;
												$tangents[$i * 4 + 1] = $reader->Byte() / 127 - 1;
												$tangents[$i * 4 + 2] = $reader->Byte() / 127 - 1;
												$tangents[$i * 4 + 3] = $reader->Byte() / 127 - 1;
												if($enableVertexColors) {
													// byte r, g, b, a
													$vertexColors[$i * 4] = $reader->Byte();
													$vertexColors[$i * 4 + 1] = $reader->Byte();
													$vertexColors[$i * 4 + 2] = $reader->Byte();
													$vertexColors[$i * 4 + 3] = $reader->Byte();
													$reader->Jump($vertexSize - 40);
												} else {
													$reader->Jump($vertexSize - 36);
												}
											}
											if($boneCount > 0) { // Envelope[vertexCount]
												$reader->Jump($vertexCount*8);
											}
											for($i = 0; $i < $faceCount; $i++) { // Face[faceCount]
												$faces[$i * 3] = $reader->UInt32LE();
												$faces[$i * 3 + 1] = $reader->UInt32LE();
												$faces[$i * 3 + 2] = $reader->UInt32LE();

												$reader->Jump($faceSize - 12);
											}
											if($lodCount <= 2) { // LodLevel[lodCount]; Lod levels are pretty much ignored if lodCount is not
												$reader->Jump($lodCount * $lodSize); // at least 3, so we can just skip reading them completely.
											} else {
												$lods = array();
												for($i = 0; $i < $lodCount; $i++) {
													$lods[$i] = $reader->UInt32LE();
													$reader->Jump($lodSize - 4);
												}
											}
											if($boneCount > 0) { // Bone[boneCount]
												$reader->Jump($boneCount * 60);
											}
											if($nameTableSize > 0) { // byte[nameTableSize]
												$reader->Jump($nameTableSize);
											}
											if($subsetCount > 0) { // MeshSubset[subsetCount]
												$reader->Jump($subsetCount*72); // subsetCount * (UInt32 * 5 + UInt16 * 26)
											}
											if($facsDataSize > 0) {
												$reader->Jump($facsDataSize);
											}
											// Convertion to mesh v1.00 (this code is old and nasty)
											$facesLength = ($lods[1] * 3) - ($lods[0] * 3);
											$actualfaces = array_slice($faces, $lods[0] * 3, $lods[1] * 3);
											$data = "version 1.00\n" . ($facesLength / 3) . "\n";
											function s($Float) { // Convert float to string
												if ($Float==null) { $Float=0; }
												$FloatScientificNotation = sprintf("%.5e", $Float);
												$FloatCleaned = str_replace("e+0", "", str_replace("e-0", "", $FloatScientificNotation));
												$sub1 = substr($FloatScientificNotation, 7, 3);
												$sub2 = substr($FloatScientificNotation, 8, 3);
												if ($sub1=="e+0" | $sub1=="e-0" | $sub1=="e-1" | $sub1=="e-2" | $sub2=="e+0" | $sub2=="e-0" | $sub2=="e-1" | $sub2=="e-2") {
													$FloatCleaned = sprintf("%g", $FloatCleaned);
												}
												return $FloatCleaned;
											}
											function addFaceToData($index, $vertices, $normals, $uvs) {
												$indexVertex = $index*3;
												$indexUV = $index*2;
														
												$data = "[" . s((float)($vertices[$indexVertex]/0.5)) . "," . s((float)($vertices[$indexVertex+1]/0.5)) . "," . s((float)($vertices[$indexVertex+2]/0.5)) . "]"; // vertex
												$data = $data . "[" . s($normals[$indexVertex]) . "," . s($normals[$indexVertex+1]) . "," . s($normals[$indexVertex+2]) . "]"; // normals
												$data = $data . "[" . s($uvs[$indexUV]) . "," . s($uvs[$indexUV+1]) . ",0]"; // uvs
												return $data;
											}
											for($i = 0; $i < $facesLength; $i += 3) {
												$data = $data . addFaceToData($actualfaces[$i], $vertices, $normals, $uvs);
												$data = $data . addFaceToData($actualfaces[$i + 1], $vertices, $normals, $uvs);
												$data = $data . addFaceToData($actualfaces[$i + 2], $vertices, $normals, $uvs);
											}

											$contents = $data;
										}
									}
								}
								break;
						}
					}

					if(!isset($_GET['version'])) {
						file_put_contents($_SERVER['DOCUMENT_ROOT']."/../assets/rbx_".$id, $contents);
					} else {
						file_put_contents($_SERVER['DOCUMENT_ROOT']."/../assets/rbx_".$id."_".$version, $contents);
					}
				}
				
			} else {
				if($id > 10420) {
					$contents = file_get_contents($_SERVER['DOCUMENT_ROOT']."/../assets/rbx_".$id.(isset($_GET['version']) ?  "_".$version : ""));
					$mimetype = checkMimeType($contents);
					
					if($mimetype == "application/gzip") {
						$contents = gzdecode($contents);
						$mimetype = checkMimeType($contents);
					}
					header("Content-Type: $mimetype");
					if(str_contains(checkMimeType($contents), "json")) {
						echo "Unauthorised access to this roblox asset!";
						file_put_contents($_SERVER['DOCUMENT_ROOT']."/../assets/rbx_".$id.(isset($_GET['version']) ?  "_".$version : ""), "");
						die(http_response_code(500));
					}
				} else {
					http_response_code(404);
					die("Asset not found!");
				}
				
			}

			Header('Content-Disposition: attachment; filename="rbx_'.$id.'"');
			echo $contents;	
		
		} else {
			http_response_code(404);
			die("Asset not found!");
		}

		
	}
?>
