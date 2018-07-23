
package com.dekconsulting.jsontozpl;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
public class convert {
	static int template=220;
	/*/takes the json string from workstation and builds the zpl to match using the settings for left,top margins and label spacing
	/public static String getzpl(String js,String Settings) throws JSONException{
		JSONObject obj=new JSONObject(js);
		//JSONObject settings = new JSONObject(Settings);
		template=obj.getJSONObject("Config").getInt("templ_id");
		obj.getJSONObject("Label").put("zpl",getAgentLabel(js,Settings));
		obj.getJSONObject("Flash").put("zpl",getFlashIndex(js, Settings, template));
		
		js=obj.toString();
		return js;
	}*/
	public static String getAgentLabel(String Ind,String Set) throws JSONException
	{
	//	int selected=Index.getInt("selected");
		JSONObject Index = new JSONObject(Ind);
		JSONObject Settings = new JSONObject(Set);
		JSONArray elements=Index.getJSONArray("elements");
		String RealtorName="";
		String FirmName="";
		String RealtorCompanyAffiliation="";
		String OfficePhone="";
		String MobilePhone="";
		
		 for(int i = 0 ; i < elements.length(); i++){
			 System.out.println(elements.getJSONObject(i).getString("id"));
			 String sId=elements.getJSONObject(i).getString("id");
			 if(sId==""){
				 //skip it
			 }
			 if(sId.equals("Realtor Name")){
				 try{
					 RealtorName=RealtorName.replace("&amp;","&");
							 
					 RealtorName=elements.getJSONObject(i).getString("text").substring(0,30);
				 
				 }catch(Exception e){
					 RealtorName=elements.getJSONObject(i).getString("text");
				 }
				 
			 }
			 if(sId.equals("Realtor Firm Name")){
				 try{
					 FirmName=FirmName.replace("&amp;","&");
					 FirmName=elements.getJSONObject(i).getString("text").substring(0,41);
				 
				 }catch(Exception e){
					 FirmName=elements.getJSONObject(i).getString("text"); 
				 }
				//FirmName=elements.getJSONObject(i).getString("text"); 
			 }
			 if(sId.equals("Realty Company Affiliation")){
				 try{
					 RealtorCompanyAffiliation=RealtorCompanyAffiliation.replace("&amp;","&");
					 RealtorCompanyAffiliation=elements.getJSONObject(i).getString("text").substring(0,41);
				 
				 }catch(Exception e){
					 RealtorCompanyAffiliation=elements.getJSONObject(i).getString("text"); 
				 }
				 
			 }
			 if(sId.equals("Office Phone")){
				 OfficePhone=elements.getJSONObject(i).getString("text");
			 }
			 if(sId.equals("Mobile Phone #")){
				MobilePhone=elements.getJSONObject(i).getString("text");
			 }
			 
		 
		 }
		String zpl ="";
		Integer top =Settings.getInt("IndexTop");
		Integer left =Settings.getInt("IndexLeft");
		Integer space =Settings.getInt("IndexSpace");
		String bfont =Settings.getString("BoldFontIndex");
		String rfont =Settings.getString("RegFontIndex");
		String bsize =Settings.getString("IndexBsize");
		String nsize =Settings.getString("IndexNsize");
		String nheight =Settings.getString("IndexNHeight");
		String csize =Settings.getString("IndexCsize");
		String cheight =Settings.getString("IndexCHeight");
		String bold =Settings.getString("FontBold");
		String light =Settings.getString("FontLight");
		try{
			int fontadj =0+0;
			if(FirmName.length()>29)
			{
				
				fontadj=-1*(FirmName.length()>40?4:FirmName.length()-30);
				csize=""+(Integer.parseInt(csize)+fontadj);
				cheight=""+(Integer.parseInt(cheight)+fontadj);
			}	else
			{	
				csize=""+(Integer.parseInt(csize)+fontadj);
				cheight=""+(Integer.parseInt(cheight)+fontadj);
			}
		}catch(Exception e){}
		String rnsize=nsize ;

		String rnheight=nheight ;
		try{
			int fontadj =0+0;
			if(RealtorCompanyAffiliation.length()>29)
			{
				
				fontadj=-1*(RealtorCompanyAffiliation.length()>40?4:RealtorCompanyAffiliation.length()-30);
				rnsize=""+(Integer.parseInt(rnsize)+fontadj);
				rnheight=""+(Integer.parseInt(rnheight)+fontadj);
			}	else
			{	
				rnsize=""+(Integer.parseInt(rnsize)+fontadj);
				rnheight=""+(Integer.parseInt(rnheight)+fontadj);
			}
		}catch(Exception e){}
		try{
			int fontadj =0+0;
			if(RealtorName.length()>29)
			{
				
				fontadj=-1*(RealtorName.length()>29?4:RealtorName.length()-30);
				bsize=""+(Integer.parseInt(bsize)+fontadj);
				//bheight=""+(Integer.parseInt(rnheight)+fontadj);
			}	else
			{	
				bsize=""+(Integer.parseInt(bsize)+fontadj);
				//rnheight=""+(Integer.parseInt(rnheight)+fontadj);
			}
		}catch(Exception e){}
		try{
			String mt="Mobile:" +(String)MobilePhone;
			String ot ="Office:" +(String)OfficePhone;
			System.out.println(mt);
			System.out.println(ot);
		if(mt.length()>ot.length())
		{
			OfficePhone =  padLeft(OfficePhone,mt.length()-ot.length());
		}else if(mt.length()>ot.length())
		{
			MobilePhone =  padLeft(MobilePhone,ot.length()-mt.length());
		}}catch(Exception e)
		{
			System.out.println(e.toString());
		}
		//label width printable is 433 dots
		//label height printable is 202 dots
		 /*
		  * move up .75 up
		  * top line 1 m to left
		  * line 2-3 left 1m
		  * office and mobile to the right 2.5
		  */
		  zpl ="^XA\n^CW1,E:" + bold + "^CW2,E:" + light;
		  top -=32;
		  left -=15;
		zpl +="^FWR\n"
				+ "^FS\n^A" + rfont + "," + nsize + "," + nheight + "^FO" + (left)+ "," +(top+20 )+ "^FB500,1,0,C,0^FDMobile: "+(MobilePhone+"                              ").substring(0,19);
		zpl +="^FS\n"+ 
				"^A" + rfont + "," + nsize + "," + nheight + "^FO" + (space+left)+ "," +(top+20 )+ "^FB500,1,0,C,0^FDMobile: "+(MobilePhone+"                              ").substring(0,19);
		;
		
		left +=30;
		zpl +="^FS\n^A" + rfont + "," + nsize + "," + (nheight )+ "^FO" + (left )+ "," +(top+28) + "^FB500,1,0,C,0^FDOffice: "+(OfficePhone+"                              ").substring(0,19);
		zpl +="^FS\n"+"^A" + rfont + "," + nsize + "," + (nheight )+ "^FO" + (space+left )+ "," +(top+28) + "^FB500,1,0,C,0^FDOffice: "+(OfficePhone+"                              ").substring(0,19);
		;
		left +=Integer.parseInt(bsize) +(Integer.parseInt(cheight)/2);
		zpl +="^FS\n^A" + rfont + "," + rnsize + "," + rnheight + "^FO" + (left-8) + "," +top + "^FB500,1,0,C,0^FD"+RealtorCompanyAffiliation;
		zpl +="^FS\n" + "^A" + rfont + "," + rnsize + "," + rnheight + "^FO" + (space+left-8) + "," +(top) + "^FB500,1,0,C,0^FD"+RealtorCompanyAffiliation;
		;
		left += (Integer.parseInt(bsize) +(Integer.parseInt(cheight)/2))/2;
		left +=9;
		zpl +="^FS\n^A" + rfont + "," + (csize) + "," + (cheight) + "^FO" + (left-8) + "," +(top) + "^FB500,1,0,C,0^FD"+FirmName;
		zpl +="^FS\n"+"^A" + rfont + "," + (csize) + "," + (cheight) + "^FO" + (space+left-8) + "," +(top) + "^FB500,1,0,C,0^FD"+FirmName;
		;
		left +=Integer.parseInt(bsize) +Integer.parseInt(cheight)/2;
		if(left>215 )left=215;
		zpl +="^FS\n^A" + bfont + "," + bsize + "," + bsize + "^FO" + (left-14) + "," +top + "^FB500,1,0,C,0^FD"+RealtorName;
		zpl +="^FS\n"+"^A" + bfont + "," + bsize + "," + bsize + "^FO" + (space+left-14) + "," +(top) + "^FB500,1,0,C,0^FD"+RealtorName;
		;
		zpl +="^FS\n^XZ";
		zpl +="\n";
		System.out.println("\nCreating Agent ID:\n");		
		
		System.out.println(zpl);
		return zpl;
	}
	public static String padRight(String s, int n) {
	    return String.format("$" + n + "s%1", s);  
	}
	public static String padLeft(String s, int n) {
	    return String.format("%1$" + n + "s", s);  
	}
	public static String getFlashIndex(String flsh,String set, int template) throws JSONException
	{
	//	int selected=Flash.getInt("selected");
		JSONObject Flash= new JSONObject(flsh);
		JSONObject Settings = new JSONObject(set);
		JSONArray elements=Flash.getJSONArray("elements");
		String Name="";
		String LastName="";
		String Street="";
		String CSZ=""; 
		String Initial=""; 
		Integer top =Settings.getInt("FlashTop");
		Integer left =Settings.getInt("FlashLeft");
	//	Integer space =Settings.getInt("FlashSpace");
		String bfont =Settings.getString("BoldFontFlash");
		String rfont =Settings.getString("RegFontFlash");
		String bsize =Settings.getString("FlashBsize");
		String nsize =Settings.getString("FlashNsize");
		String nheight =Settings.getString("FlashNHeight");
		String bold =Settings.getString("FontBold");
		String light =Settings.getString("FontLight");
	//	String csize =Settings.getString("FlashCsize");
		//String asize =Settings.getString("FlashInitialsize");
	//	int labelwidth=388+space;
	//	int cartouchewidth=388;
	//	int cartoucheheight=132;
		
		System.out.println("\nCreating Label Design:" + template + "\n");		
		 for(int i = 0 ; i < elements.length(); i++){
			 String sId=elements.getJSONObject(i).getString("id");
			 if(sId==""){
				 //skip it
			 }
			 if(sId.equals("name")){
				Name=elements.getJSONObject(i).getString("text");
				try
				{
				LastName=Name;//.split(" ")[Name.split(" ").length-1];
				}catch(Exception e){
					LastName="";
				}
			 }
			 if(sId.equals("city,state,zip")){
					CSZ=elements.getJSONObject(i).getString("text");
				 }
			 if(sId.equals("street address")){
					Street=elements.getJSONObject(i).getString("text");
				 }
			 if(sId.equals("initial")){
				 Initial=elements.getJSONObject(i).getString("text");
				 }
		 
		 }
		String zpl ="^XA\n^CW1,E:" + bold + "\n^CW2,E:" + light;
		switch(template)
		{
		case 220:{ //crown
			//Name=(Name+"                                       ").substring(0,31);
			Street=(Street+"                                       ").substring(0,32).trim();
			CSZ=(CSZ+"                                       ").substring(0,30).trim();
			//last name

			Integer boxwidth=442; 
			left=0; 
			int fontadj =9+0;
			if(LastName.length()>14)
			{
				
				fontadj=9-(LastName.length()>23?23:LastName.length()-14);
				bsize=""+(Integer.parseInt(bsize)+fontadj);
			}	else
			{	
				bsize=""+(Integer.parseInt(bsize)+fontadj);
			}
			top =top/2;
			top +=14;
			/*
			 * zpl +="\n^FO422,0^GB108,1,2,B,2^FS";
			zpl +="\n^FO0,108^GB2,422,2,B,2^FS";
			*/
			zpl +="^A" + bfont + "n," + bsize + "," + (Integer.parseInt(bsize)-2) + "^FO0," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+ LastName.toUpperCase() + "^FS";
		 
			top +=40;
			zpl +="\n^FO" + (17) + ","+ top +"^GB388,4,2,B,2^FS";

		 	//line
			top +=16;
			//street
			zpl +="\n^A" + rfont + "n," + nheight + "," + nsize + "^FO0," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+Street + "^FS";
		 	top +=27;
			zpl +="\n^A" + rfont + "n," + (Integer.parseInt(nheight)) + "," +  (Integer.parseInt(nsize)) + "^FO0," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+CSZ + "^FS";
			 
			//address
			break;}
		case 221:{//classic
			Name=(Name+"                                       ").substring(0,31).trim();
			Street=(Street+"                                       ").substring(0,30).trim();
			CSZ=(CSZ+"                                       ").substring(0,31).trim();
			top =top/2;
		//	Integer boxsize=108; 
			Integer boxwidth=422; 
			left =0;
			top +=28;
			boxwidth =422;
			//fullname
			zpl +="\n^A" + rfont + "n," + (Integer.parseInt(nheight)) + "," + (Integer.parseInt(nsize)) + "^FO" +( left) + "," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+ Name + "^FS";
			top +=27;
			//street
			zpl +="\n^A" + rfont + "n," + (Integer.parseInt(nheight)) + "," + (Integer.parseInt(nsize)) + "^FO" + left + "," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+Street+ "^FS";
		 	top +=27;
			zpl +="\n^A" + rfont + "n," +  (Integer.parseInt(nheight)) + "," + (Integer.parseInt(nsize)) + "^FO" + left + "," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+ CSZ  + "^FS";
		 //CSZ
			break;}
		case 222:{//cartouche 404 x 141
			//5.71
			//0.86
			//in beveled rectangle 
			Name=(Name+"                                       ").substring(0,31).trim();
			Street=(Street+"                                       ").substring(0,30).trim();
			CSZ=(CSZ+"                                       ").substring(0,31).trim();
			top =top/2; 
			Integer boxsize=108; 
			Integer boxwidth=388; 
			left=28;
			top =top +10;
			zpl +="\n^FO" + (left-8) + ","+ top +"^GB" + boxwidth + "," + boxsize + ",3,B,2^FS";
			//zpl +="^FO" + (left+labelwidth) + "," +(top) +"^GB320,132,3,B,2^FS";
			left =6;
			top +=16;
			boxwidth =422;
			//fullname
			zpl +="\n^A" + rfont + "n," + (Integer.parseInt(nheight)) + "," + (Integer.parseInt(nsize)-1) + "^FO" +( left) + "," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+ Name + "^FS";

		 
			top +=27;
			//street
			zpl +="\n^A" + rfont + "n," + (Integer.parseInt(nheight)) + "," + (Integer.parseInt(nsize)-1) + "^FO" + left + "," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+Street+ "^FS";
		 	top +=27;
			zpl +="\n^A" + rfont + "n," +  (Integer.parseInt(nheight)) + "," + (Integer.parseInt(nsize)-1) + "^FO" + left + "," +top + "^FB" + boxwidth + ",1,0,C,0^FD"+ CSZ  + "^FS";
		 	//CSZ
			break;}
		case 223:{//MonoGram
			//initial in beveled square
			Name=(Name+"                                       ").substring(0,26);
			Street=(Street+"                                       ").substring(0,25);
			CSZ=(CSZ+"                                       ").substring(0,26);
			left -=1;
			zpl +="\n^FO" + left + ","+ top +"^GB80,80,3,B,2^FS"; 
			String fontwidth ="100";
			if(Initial.compareTo("W")==0)fontwidth="75";
			if( Initial.compareTo("M")==0)fontwidth="80";
			 
			if(Initial.compareTo("I")==0 || Initial.compareTo("F")==0 || Initial.compareTo("J")==0 )
				zpl +="\n^A" + bfont + "n,90," +fontwidth +"^FO" + (left+16 ) + "," +(top+5) + "^FB80,1,0,C,0^FD"+Initial.toUpperCase() + "^FS";
			else
				zpl +="\n^A" + bfont + "n,90," +fontwidth +"^FO" + (left ) + "," +(top+5) + "^FB80,1,0,C,0^FD"+Initial.toUpperCase() + "^FS";
			//then on right
			//fullname 
			left +=85;
			//top +=1;
			zpl +="\n^A" + rfont + "n," + nheight + "," + nsize + "^FO" +  (left) + "," +top + "^FB315,1,0,L,0^FD"+Name + "^FS";
		 	top +=30;
			//street
		 	zpl +="\n^A" + rfont + "n," + nheight + "," + nsize + "^FO" +  (left) + "," +top + "^FB315,1,0,L,0^FD"+Street + "^FS";
		 	top +=28;
			zpl +="\n^A" + rfont + "n," + nheight + "," + (Integer.parseInt(nsize)-1) + "^FO" + (left) + "," +top + "^FB315,1,0,L,0^FD"+CSZ + "^FS";
		 
			//CSZ
			break;}
		}
		
		zpl+="\n^XZ";
		zpl+=zpl;
		System.out.println(zpl);
		return zpl;
	}
}
