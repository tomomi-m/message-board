<?php
class SiteImage {
	const IMAGE_EMOTIONS = "image/site/chat/emotions/";

	function createImageFile($siteId, $imageDataStr) {
		$imageParsed = $this->parseImageDataStr ( $imageDataStr );
		$imageNewPath = $this->getNewImagePath ( $siteId );
		$fileName = $imageNewPath ['imageFileNameNoExt'] . "." . $imageParsed ['imgExt'];
		$filePathAbs = $imageNewPath ['imageDirAbs'] . "/" . $fileName;

		$fp = fopen ( $filePathAbs, 'w' );
		fwrite ( $fp, $imageParsed ['imgBinary'] );
		fclose ( $fp );
		return $imageNewPath ['imageDir'] . "/" . $fileName;
	}
	public function createImageFileMaxSizeFix($siteId, $imageDataStr, $squareSize = 48) {
		$imageParsed = $this->parseImageDataStr ( $imageDataStr );
		$imageNewPath = $this->getNewImagePath ( $siteId );
		$fileName = $imageNewPath ['imageFileNameNoExt'] . "." . $imageParsed ['imgExt'];
		$filePathAbs = $imageNewPath ['imageDirAbs'] . "/" . $fileName;

		$imageResource = imagecreatefromstring ( $imageParsed ['imgBinary'] );
		$newWidth = $width = imagesx ( $imageResource );
		$newHeight = $height = imagesy ( $imageResource );
		if ($width > $squareSize || $height > $squareSize) {
			if ($newWidth > $squareSize) {
				$newHeight = floor ( $newHeight * $squareSize / $newWidth );
				$newWidth = $squareSize;
			}
			if ($newHeight > $squareSize) {
				$newWidth = floor ( $newWidth * $squareSize / $newHeight );
				$newHeight = $squareSize;
			}
			$square_new = imagecreatetruecolor ( $newWidth, $newHeight );
			switch ($imageParsed ['imgExt']) {
				case 'gif' :
					$trnprt_indx = imagecolortransparent ( $imageResource );
					// 透過色RGB値の取得
					$trnprt_color = imagecolorsforindex ( $imageResource, $trnprt_indx );
					// 修正後画像での透過色（にする色）のインデックスを取得
					$trnprt_indx = imagecolorallocate ( $square_new, $trnprt_color ['red'], $trnprt_color ['green'], $trnprt_color ['blue'] );
					// imagecreatetruecolor で作った画像は背景が黒 <- 先の回答時点ではこれがわかっていなかった
					// 透過色インデックスで塗りつぶす
					imagefill ( $square_new, 0, 0, $trnprt_indx );
					// 透過色インデックスを透過色に指定
					imagecolortransparent ( $square_new, $trnprt_indx );
					break;
				case 'png' :
					$bg_color = imagecolorallocatealpha ( $square_new, 255, 255, 255, 127 );
					imagefill ( $square_new, 0, 0, $bg_color );
					break;
			}
			imagecopyresampled ( $square_new, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height );

			switch ($imageParsed ['imgExt']) {
				case 'jpg' :
					imagejpeg ( $square_new, $filePathAbs );
					break;
				case 'gif' :
					imagegif ( $square_new, $filePathAbs );
					break;
				case 'png' :
					imagealphablending ( $square_new, false );
					imagesavealpha ( $square_new, true );
					imagepng ( $square_new, $filePathAbs );
					break;
			}
			imagedestroy ( $square_new );
		} else {
			$fp = fopen ( $filePathAbs, 'w' );
			fwrite ( $fp, $imageParsed ['imgBinary'] );
			fclose ( $fp );
		}
		imagedestroy ( $imageResource );
		return $imageNewPath ['imageDir'] . "/" . $fileName;
	}
	public function createImageFileFixedSizeJpg($siteId, $imageDataStr, $fileNamePostfix = "-th", $squareSize = 80) {
		$imageParsed = $this->parseImageDataStr ( $imageDataStr );
		$imageNewPath = $this->getNewImagePath ( $siteId );
		$fileName = $imageNewPath ['imageFileNameNoExt'] . $fileNamePostfix . ".jpg";
		$filePathAbs = $imageNewPath ['imageDirAbs'] . "/" . $fileName;

		$imageResource = imagecreatefromstring ( $imageParsed ['imgBinary'] );
		$this->squareAndSaveImageResouceJpg ( $imageResource, $filePathAbs );
		imagedestroy ( $imageResource );
		return $imageNewPath ['imageDir'] . "/" . $fileName;
	}
	function squareAndSaveImageResouceJpg($imageResource, $filePathAbs, $squareSize = 80) {
		$width = imagesx ( $imageResource );
		$height = imagesy ( $imageResource );

		if ($width >= $height) {
			// 横長の画像の時
			$side = $height;
			$x = floor ( ($width - $height) / 2 );
			$y = 0;
			$width = $side;
		} else {
			// 縦長の画像の時
			$side = $width;
			$y = floor ( ($height - $width) / 2 );
			$x = 0;
			$height = $side;
		}
		$square_new = imagecreatetruecolor ( $squareSize, $squareSize );
		imagecopyresampled ( $square_new, $imageResource, 0, 0, $x, $y, $squareSize, $squareSize, $width, $height );
		imagejpeg ( $square_new, $filePathAbs );
		imagedestroy ( $square_new );
	}
	function resizeAndSaveImageResouceJpg($imageResource, $filePathAbs, $maxWidth = 160, $maxHeight = 160, $quality = 85) {
		$width = imagesx ( $imageResource );
		$height = imagesy ( $imageResource );

		$newWidth = $width;
		$newHeight = $height;
		if ($newWidth > $maxWidth) {
			$newWidth = $maxWidth;
			$newHeight = $height * $newWidth / $width;
		}
		if ($newHeight > $maxHeight) {
			$newHeight = $maxHeight;
			$newWidth = $width * $newHeight / $height;
		}
		$imageNew = imagecreatetruecolor ( $newWidth, $newHeight );
		imagecopyresampled ( $imageNew, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height );

		imagejpeg ( $imageNew, $filePathAbs, $quality );
		imagedestroy ( $imageNew );
	}
	public function createImageFileAndThumbnailFileJpg($siteId, $imageDataStr, $squareSize = 1920, $squareSizeTh = 200) {
		$imageParsed = $this->parseImageDataStr ( $imageDataStr );
		$imageNewPath = $this->getNewImagePath ( $siteId );
		$fileName = $imageNewPath ['imageFileNameNoExt'] . ".jpg";
		$filePathAbs = $imageNewPath ['imageDirAbs'] . "/" . $fileName;
		$fileNameTh = $imageNewPath ['imageFileNameNoExt'] . "-th.jpg";
		$filePathThAbs = $imageNewPath ['imageDirAbs'] . "/" . $fileNameTh;

		$imageResource = imagecreatefromstring ( $imageParsed ['imgBinary'] );
		if ($imageParsed ['angle']) {
			$imageResource = imagerotate($imageResource, $imageParsed ['angle'], 0);
		}
		$this->resizeAndSaveImageResouceJpg ( $imageResource, $filePathAbs, $squareSize, $squareSize );
		$this->resizeAndSaveImageResouceJpg ( $imageResource, $filePathThAbs, $squareSizeTh, $squareSizeTh, 60 );
		imagedestroy ( $imageResource );
		return array (
				'base' => $imageNewPath ['imageDir'] . "/" . $fileName,
				'th' => $imageNewPath ['imageDir'] . "/" . $fileNameTh
		);
	}
	function parseImageDataStr($imageDataStr) {
		if (stripos ( $imageDataStr, 'data:image/', 0 ) === false) {
			throw new Exception ( 'invalid argument:not start "data:image/"' );
		}
		$i = 0;
		$st = stripos ( $imageDataStr, ';', $i );
		$mime_type = substr ( $imageDataStr, $i + 5, $st - 5 - $i );
		$i = $st + 1;
		$st = stripos ( $imageDataStr, ',', $i );
		$enc = substr ( $imageDataStr, $i, $st - $i );
		$i = $st + 1;
		$st = strlen ( $imageDataStr );
		$imgBinaryStr = substr ( $imageDataStr, $i, $st - $i );
		$imgBinary = base64_decode ( $imgBinaryStr );

		$IMAGE_MIME_TYPES = array (
				'gif' => 'image/gif',
				'jpg' => 'image/jpeg',
				'png' => 'image/png'
		);
		if (! ($imgExt = array_search ( $mime_type, $IMAGE_MIME_TYPES, true ))) {
			throw new Exception ( "Unvalid mime-type:" . $mime_type );
		}
		$angle = 0;
		if ($imgExt == 'jpg') {
			$pelData = new lsolesen\pel\PelDataWindow($imgBinary);
			$pel = new lsolesen\pel\PelJpeg($pelData);
			$exif = $pel->getExif();
			if ($exif) {
				$tiff = $exif->getTiff();
				$ifd0 = $tiff->getIfd();
				if ($ifd0) {
					$orientationEntry = $ifd0->getEntry(lsolesen\pel\PelTag::ORIENTATION);
					if ($orientationEntry) {
						$orientation = $orientationEntry->getValue();
						$angle = 0;
						switch($orientation) {
							case 3: // 180 rotate left
								$angle = 180;
								break;
							case 6: // 90 rotate right
								$angle = -90;
								break;
							case 8:    // 90 rotate left
								$angle = 90;
								break;
						}
					}
				}
			}
			$pel->clearExif();
			$imgBinary = $pel->getBytes();
		}

		return array (
				'mimeType' => $mime_type,
				'encoding' => $enc,
				'imgBinary' => $imgBinary,
				'imgExt' => $imgExt,
				'angle' => $angle
		);
	}
	function getNewImagePath($siteId) {
		$imageId = $this->getNewImageId ()[0]->ID;
		Log::info ( "getNewImagePath", [
				$siteId,
				$imageId
		] );
		$imageDir = '${siteImage}/' . str_pad ( ($imageId - ($imageId % 1000)), 8, "0", STR_PAD_LEFT );
		$imageFileNameNoExt = str_pad ( $imageId, 8, "0", STR_PAD_LEFT );

		$imageDirAbs = public_path ( str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $siteId, $imageDir ) );
		if (! file_exists ( $imageDirAbs ))
			mkdir ( $imageDirAbs, 0755, true );
		return array (
				'imageDir' => $imageDir,
				'imageFileNameNoExt' => $imageFileNameNoExt,
				'imageDirAbs' => $imageDirAbs
		);
	}
	function getNewImageId() {
		$id = null;
		DB::transaction ( function () use(&$id) {
			DB::update ( "UPDATE seq_image SET id=LAST_INSERT_ID(id+1);" );
			$id = DB::select ( "SELECT LAST_INSERT_ID() ID" );
		} );
		return $id;
	}

	public function getFaces(Site $site) {
		$folders = array ();
		$userIcons = SiteUserIcon::where ( 'site', $site->id )->where ( 'userId', MySession::getUserId ( $site->id ) )->orderby ( 'order' )->get ();
		$userIconImgs = array ();
		if (count ( $userIcons )) {
			foreach ( $userIcons as $userIcon ) {
				$userIconImgs [] = mb_substr ( str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $userIcon->icon ), 1 );
			}
			$folders [] = array (
					"name" => "個人設定",
					"top" => $userIconImgs [0],
					"all" => $userIconImgs
			);
		}
		return $folders;
	}


	public function getEmotionCatalog(Site $site) {
		$folders = array ();
		$dir = public_path ( self::IMAGE_EMOTIONS );
		$files = scandir ( $dir );
		sort ( $files );
		foreach ( $files as $file ) {
			$folder = null;
			if ($file == "." || $file == "..")
				continue;
			if (is_dir ( $dir . $file )) {
				$images = scandir ( $dir . $file );
				sort ( $images );
				foreach ( $images as $image ) {
					if (preg_match ( "/(gif|jpg|png)$/", $image )) {
						if (! $folder) {
							$folder = array (
									"name" => $file,
									"top" => self::IMAGE_EMOTIONS . $file . "/" . $image,
									"all" => array ()
							);
						}
						$folder ["all"] [] = self::IMAGE_EMOTIONS . $file . "/" . $image;
					}
				}
			}
			if ($folder)
				$folders [] = $folder;
		}
		return $folders;
	}

}