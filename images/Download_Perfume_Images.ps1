$ErrorActionPreference = 'Continue'
$root = Join-Path $PSScriptRoot 'Perfume_Images'
New-Item -ItemType Directory -Force -Path $root | Out-Null

$items = @(
    @{ Name='01_Dior_Sauvage_Elixir.png'; Url='https://beautyhouse.com/cdn/shop/files/03t2yh7xso_1600x.png?v=1738779277' },
    @{ Name='02_Bleu_de_Chanel_EDP.jpg'; Url='https://www.sephora.com/productimages/sku/s1695972-main-zoom.jpg' },
    @{ Name='03_YSL_MYSLF_EDP.jpg'; Url='https://media.sephora.eu/content/dam/digital/pim/published/Y/YVES_SAINT_LAURENT/701839/326687-media_swatch.jpg?scaleHeight=undefined&scaleMode=undefined&scaleWidth=undefined' },
    @{ Name='04_YSL_Y_EDP.jpg'; Url='https://www.yslbeauty.com/dw/image/v2/BDCR_PRD/on/demandware.static/-/Sites-ysl-master-catalog/en/dw680410df/images/PACKSHOTS/FRAGRANCE/FOR_HIM/WW-50194YSL_y-edp-refill/00-3614273128971_200ml_y-eau-de-parfum_Alt1.jpg' },
    @{ Name='05_Armani_Acqua_di_Gio_Profondo_EDP.jpg'; Url='https://www.giorgioarmanibeauty.com.au/dw/image/v2/BFZM_PRD/on/demandware.static/-/Sites-armani-master/default/dw35f91c5e/images/Fragrance/ww-00848-arm/ww-00848-arm-3614273953832-200ml-IMAGE1.jpg' },
    @{ Name='06_Dior_Homme_Intense.jpg'; Url='https://www.sephora.fr/dw/image/v2/BCVW_PRD/on/demandware.static/-/Sites-masterCatalog_Sephora/default/dwa6fba59d/images/hi-res/PID_principal/PID_principal_3/P53514_principal.jpg?q=75' },
    @{ Name='07_JPG_Le_Male_Elixir.jpg'; Url='https://f.fcdn.app/imgs/c1cf1a/www.sanroque.com.uy/sanruy/4f20/original/catalogo/133509_133509_1/2000-2000/perfume-jean-paul-gaultier-le-male-elixir-edp-125ml-perfume-jean-paul-gaultier-le-male-elixir-edp-125ml.jpg' },
    @{ Name='08_Viktor_Rolf_Spicebomb_Extreme.webp'; Url='https://fandi-perfume.com/cdn/shop/files/viktor-rolf-spicebomb-extreme-eau-de-parfum-1223828980.webp?v=1772041811' },
    @{ Name='09_Armani_Stronger_With_You_Intensely.jpg'; Url='https://www.parfumerie.nl/media/catalog/product/a/r/armani-stronger-with-you-intensely-eau-de-parfum-spray.jpg' },
    @{ Name='10_Terre_d_Hermes_EDT.jpg'; Url='https://cdn11.bigcommerce.com/s-3c0gw9unrd/images/stencil/1280x1280/products/40601/118658/2044726_1_en_GB__39243.1751270645.jpg?c=1' },
    @{ Name='11_Kilian_Angels_Share.png'; Url='https://www.perfumenz.co.nz/cdn/shop/products/kilian-angels-share_1200x1200.png?v=1666139142' },
    @{ Name='12_Creed_Aventus.webp'; Url='https://www.ausliebezumduft.de/cdn/shop/files/Aventus100ml.webp?v=1780988265' },
    @{ Name='13_PDM_Layton.png'; Url='https://anforaparfums.com/cdn/shop/files/PARFUMSDEMARLY-LAYTON_8612da6e-91cf-4858-b4b8-597b5380fc68.png?v=1774191042&width=1024' },
    @{ Name='14_MFK_Baccarat_Rouge_540_Extrait.jpg'; Url='https://media.sephora.eu/content/dam/digital/pim/published/M/MAISON_FRANCIS_KURKDJIAN/427450/239831-media_swatch.jpg?scaleHeight=undefined&scaleMode=undefined&scaleWidth=undefined' },
    @{ Name='15_Tom_Ford_Tobacco_Vanille.png'; Url='https://www.tomfordbeauty.com/cdn/shop/files/tf_sku_T01K01_2000x2000_0.png?v=1773983721&width=2000' },
    @{ Name='16_Louis_Vuitton_Imagination.jpg'; Url='https://boutiquederoyal.com/cdn/shop/files/1_54ef82e8-aa27-413c-a014-d5cc303c0efa_1400x.jpg?v=1752325086' },
    @{ Name='17_Xerjoff_Naxos_1861.jpg'; Url='https://artofessenceltd.com/cdn/shop/files/IMG_5592.jpg?v=1750798305' },
    @{ Name='18_Amouage_Reflection_Man.jpg'; Url='https://cdn.media.amplience.net/i/frasersdev/75509169_o?%24h-ttl%24=&fmt=auto&h=992&sm=scaleFit&upscale=true&w=992' },
    @{ Name='19_Tom_Ford_Oud_Wood.jpg'; Url='https://img.kingpowerclick.com/cdn-cgi/image/format%3Dauto/kingpower-com/image/upload/w_640%2Ch_640/v1750824489/prod/1091010-L1.jpg' },
    @{ Name='20_Initio_Side_Effect.png'; Url='https://www.meister-parfumerie.de/cdn/shop/files/initio-parfums-priv-s-side-effect-edp-nat-spray-3701415901391-48408487919882.png?v=1771262799' },
    @{ Name='21_Louis_Vuitton_Ombre_Nomade.jpg'; Url='https://www.dekantparfum.com.tr/idea/hs/04/myassets/products/695/louis-vuitton-ombre-nomade.jpg?revision=1762240489' },
    @{ Name='22_PDM_Herod.jpg'; Url='https://www.parfuemerie-heudorf-shop.de/media/08/3d/49/1699028663/Marly%20Herod%20125ml.jpg' },
    @{ Name='23_Nishane_Hacivat.jpg'; Url='https://www.hype-network.de/cdn/shop/files/hacivat.jpg?v=1723726136' },
    @{ Name='24_Creed_Green_Irish_Tweed.png'; Url='https://perfumes.ec/cdn/shop/files/CreedGreenIrishTweed100ml_6cc2805b-d538-4b2e-b39c-5fe780f88384.png?v=1713146703' },
    @{ Name='25_Guerlain_L_Homme_Ideal_Parfum.jpg'; Url='https://www.myfragrancesamples.com/cdn/shop/files/1-Photoroom_bcf8cfba-b483-4253-82a3-b72f0c91111c.jpg?v=1758559976' },
    @{ Name='26_Amouage_Interlude_Man.jpg'; Url='https://krystalfragrance.com/cdn/shop/products/InterludeMan.jpg?v=1678822704' }
)

$ok = 0
$failed = @()
foreach ($item in $items) {
    $destination = Join-Path $root $item.Name
    try {
        Invoke-WebRequest -Uri $item.Url -OutFile $destination -UseBasicParsing -TimeoutSec 45
        if ((Get-Item $destination).Length -gt 1000) { $ok++ }
        else { Remove-Item $destination -Force; $failed += $item.Name }
    }
    catch {
        if (Test-Path $destination) { Remove-Item $destination -Force }
        $failed += $item.Name
        Write-Warning "Download failed: $($item.Name) - $($_.Exception.Message)"
    }
}

$zip = Join-Path $PSScriptRoot 'Perfume_Images_26_Products.zip'
if (Test-Path $zip) { Remove-Item $zip -Force }
if ((Get-ChildItem $root -File).Count -gt 0) {
    Compress-Archive -Path (Join-Path $root '*') -DestinationPath $zip -Force
}
Write-Host "Downloaded: $ok / $($items.Count)"
Write-Host "Image folder: $root"
if (Test-Path $zip) { Write-Host "ZIP created: $zip" }
if ($failed.Count -gt 0) {
    Write-Host 'Failed files:'
    $failed | ForEach-Object { Write-Host " - $_" }
    Write-Host 'Check the failed URLs manually; some stores may block automated downloads.'
}
