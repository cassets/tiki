{* $Id$ *}

{remarksbox type="tip" title="{tr}Tip{/tr}"}{tr}Copyright allows to determine a copyright for all the objects of tiki{/tr}.{/remarksbox}

<form action="tiki-admin.php?page=copyright" method="post">
    <div class="row">
        <div class="form-group col-lg-12 clearfix">
            <div class="pull-right">
                <input type="submit" class="btn btn-default btn-sm" value="{tr}Change preferences{/tr}">
            </div>
        </div>
    </div>

    <input type="hidden" name="setcopyright" />
	
	<fieldset class="table">
		<legend>{tr}Activate the feature{/tr}</legend>
		{preference name=feature_copyright visible="always"}
	</fieldset>

	<fieldset>
		<legend>{tr}Copyright management{/tr}</legend>
		{preference name=wikiLicensePage}
		{preference name=wikiSubmitNotice}

		<div class="adminoptionbox">
			<div class="adminoptionlabel">{tr}Enable copyright management for:{/tr}</div>
			<div class="adminoptionboxchild">
				{preference name=wiki_feature_copyrights}
				{preference name=articles_feature_copyrights}
				{preference name=blogs_feature_copyrights}
				{preference name=faqs_feature_copyrights}
			</div>
		</div>
	</fieldset>

    <br>{* I cheated. *}
    <div class="row">
        <div class="form-group col-lg-12 text-center">
            <input type="submit" class="btn btn-default btn-sm" value="{tr}Change preferences{/tr}">
        </div>
    </div>
</form>
