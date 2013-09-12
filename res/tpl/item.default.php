<style type="text/css">
<!--
@res:css:item.css;
-->
</style>
<div class="wrapper-item">
 <h1 class="tree"><span>@podio:company-or-organisation:0:value;</span></h1>
 <div class="view view-spot">
  <div class="streetview"></div>
  <table>
   <tr>
    <td><a class="action active" href="#"><img src="@res:img:icons/all/holo_dark/xhdpi/7_location_place.png;" /></a></td>
    <td><a class="action" href="tel:@podio:telefon:value;"><img src="@res:img:icons/all/holo_dark/xhdpi/10-device-access-call.png;" /></a></td>
    <td><a class="action" href="#"><img src="@res:img:icons/all/holo_dark/xhdpi/7_location_web_site.png;" /></a></td>
    <td><a class="action" href="#"><img src="@res:img:icons/all/holo_dark/xhdpi/7-location-directions.png;" /></a></td>
   </tr>
  </table>
  <div class="block">
   <h2>@podio:company-or-organisation:value;</h2>
   <p>
    <span>@podio:street-address:value;</span><br />
    <span>@podio:zip-codepost-code:value;</span> <span>@podio:city:value;</span><br />
   </p>
   <?php if (strlen('@podio:telefon:value;')): ?><p>Tel.: @podio:telefon:value;</p><?php endif; ?>
  </div>
 </div>
</div>