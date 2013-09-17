<style type="text/css">
<!--
@res:css:item.css;
-->
</style>
<div class="wrapper-item">
 <h1 class="tree"><span>@json:company-or-organisation:values:0:value;</span></h1>
 <div class="view view-spot">
  <div class="streetview"></div>
  <table>
   <tr>
    <td><a class="action active" href="#"><img src="@res:img:icons/all/holo_dark/xhdpi/7_location_place.png;" /></a></td>
    <?php if ('@json:telefon:values:?count;' != '0'): ?><td><a class="action" href="tel:@json:telefon:values:0value;"><img src="@res:img:icons/all/holo_dark/xhdpi/10-device-access-call.png;" /></a></td><?php endif; ?>
    <td><a class="action" href="#"><img src="@res:img:icons/all/holo_dark/xhdpi/7_location_web_site.png;" /></a></td>
    <td><a class="action" href="#"><img src="@res:img:icons/all/holo_dark/xhdpi/7-location-directions.png;" /></a></td>
   </tr>
  </table>
  <div class="block">
   <h2>@json:company-or-organisation:values:0:value;</h2>
   <p>
    <span>@json:street-address:values:0:value;</span><br />
    <span>@json:zip-codepost-code:values:0:value;</span> <span>@json:city:values:0:value;</span><br />
   </p>
   <?php if ('@json:telefon:values:?count;' != '0'): ?><p>Tel.: @json:telefon:values:0:value;</p><?php endif; ?>
  </div>
 </div>
</div>