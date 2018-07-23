package com.dekconsulting.jsontozpl;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;

import org.json.JSONException;
import org.junit.Test;

import junit.framework.TestCase;

public class mytest extends TestCase {
	@Test
	public void test() {
		String j="";
		String ind ="";
		String s;
		Integer templ =200;
		// test for 223 
	//	s="{FontBold:'FRU000.FNT',FontLight:'FRU001.FNT',IndexTop:'100',IndexLeft:'100',IndexSpace:'150',BoldFontIndex:'2',RegFontIndex:'1',IndexBsize:'36',IndexNsize:'34',IndexCsize:'40',IndexCHeight:'40',IndexNHeight:'30',FlashTop:'100',FlashLeft:'32',FlashSpace:'56',BoldFontFlash:'2',RegFontFlash:'1',FlashBsize:'72',FlashNsize:'24',FlashCsize:'13',FlashNHeight:'24'}";
		// test for 222 
	//	s="{FontBold:'FRU000.FNT',FontLight:'FRU001.FNT',IndexTop:'100',IndexLeft:'100',IndexSpace:'150',BoldFontIndex:'2',RegFontIndex:'1',IndexBsize:'36',IndexNsize:'34',IndexCsize:'40',IndexCHeight:'40',IndexNHeight:'30',FlashTop:'100',FlashLeft:'32',FlashSpace:'56',BoldFontFlash:'2',RegFontFlash:'1',FlashBsize:'30',FlashNsize:'22',FlashCsize:'13',FlashNHeight:'28'}";
		// test for 221 
		//s="{FontBold:'FRU000.FNT',FontLight:'FRU001.FNT',IndexTop:'100',IndexLeft:'100',IndexSpace:'150',BoldFontIndex:'2',RegFontIndex:'1',IndexBsize:'36',IndexNsize:'34',IndexCsize:'40',IndexCHeight:'40',IndexNHeight:'30',FlashTop:'100',FlashLeft:'32',FlashSpace:'56',BoldFontFlash:'2',RegFontFlash:'1',FlashBsize:'30',FlashNsize:'24',FlashCsize:'13',FlashNHeight:'24'}";
		// test for 220 
 	 	//s="{FontBold:'FRU000.FNT',FontLight:'FRU001.FNT',IndexTop:'100',IndexLeft:'100',IndexSpace:'150',BoldFontIndex:'2',RegFontIndex:'1',IndexBsize:'36',IndexNsize:'34',IndexCsize:'40',IndexCHeight:'40',IndexNHeight:'30',FlashTop:'100',FlashLeft:'25',FlashSpace:'56',BoldFontFlash:'2',RegFontFlash:'1',FlashBsize:'28',FlashNsize:'26',FlashCsize:'13',FlashNHeight:'25',FlashInitialsize:'72'}";
		//for proofs
	 	s="{FontBold:'FRU000.FNT',FontLight:'FRU001.FNT',IndexTop:'65',IndexLeft:'45',IndexSpace:'250',BoldFontIndex:'2',RegFontIndex:'1',IndexBsize:'32',IndexNsize:'34',IndexCsize:'34',IndexCHeight:'34',IndexNHeight:'34',FlashTop:'45',FlashLeft:'25',FlashSpace:'109',BoldFontFlash:'2',RegFontFlash:'2',FlashBsize:'28',FlashNsize:'24',FlashCsize:'13',FlashNHeight:'24',FlashInitialsize:'80'}";
		
		try {
			j=readUsingBufferedReader("C:\\Users\\David\\Documents\\Projects\\realtygifts\\jsonagent.txt");

			ind=readUsingBufferedReader("C:\\Users\\David\\Documents\\Projects\\realtygifts\\jsonindex.txt");

			System.out.println(j);
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		String z=new String();
		try {
			z=convert.getAgentLabel(j.toString(),s);
			for(templ=220;templ <224;templ++){
			z=convert.getFlashIndex(ind, s, templ);
			}
		} catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		System.out.println(z);
	}
	  private static String readUsingBufferedReader(String fileName) throws IOException {
	        BufferedReader reader = new BufferedReader( new FileReader (fileName));
	        String line = null; 
	        StringBuilder stringBuilder = new StringBuilder();
	        String ls = System.getProperty("line.separator");
	        while( ( line = reader.readLine() ) != null ) {
	            stringBuilder.append( line );
	            stringBuilder.append( ls );
	        }
	        //delete the last ls
	        stringBuilder.deleteCharAt(stringBuilder.length()-1);
	        reader.close();
	        return stringBuilder.toString();
	    }

}
