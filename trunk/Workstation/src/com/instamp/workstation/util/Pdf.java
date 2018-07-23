package com.instamp.workstation.util;

import java.awt.Graphics2D;
import java.awt.geom.AffineTransform;
import java.awt.image.BufferedImage;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;

import javax.imageio.ImageIO;

import com.itextpdf.text.Document;
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Element;
import com.itextpdf.text.Image;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.pdf.*;
import com.itextpdf.text.pdf.parser.*;
import com.vaadin.server.StreamResource.StreamSource;
import com.itextpdf.text.Utilities;

public class Pdf implements StreamSource {
    /**
	 * 
	 */
	private static final long serialVersionUID = -5234597299617139717L;
	private final ByteArrayOutputStream os = new ByteArrayOutputStream();

	private Document document;
	private com.itextpdf.text.pdf.PdfContentByte canvas;
	private Rectangle _size;

    public Pdf(Rectangle size) {
        document = null;
        _size = size;
        try {
            document = new Document(size);
            PdfWriter writer = PdfWriter.getInstance(document, os);
            document.open();
            canvas = writer.getDirectContent();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    public Pdf(Rectangle size, int r, int g, int b) {
        document = null;
        _size = size;
        try {
            document = new Document(size);
            PdfWriter writer = PdfWriter.getInstance(document, os);
            document.open();
            canvas = writer.getDirectContent();
            setBackgroundColour(r, g, b);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void setBackgroundColour(int r, int g, int b) {
        canvas.setRGBColorFill(r, g, b);
        canvas.moveTo(0, _size.getTop());
        canvas.lineTo(0, 0);
        canvas.lineTo(_size.getRight(), 0);
        canvas.lineTo(_size.getRight(), _size.getTop());
        canvas.lineTo(0, _size.getTop());
        canvas.fill();
    }
    
    public void addElement(Element e) throws DocumentException {
    	document.add(e);
    }
    
    public float getStringWidth(String str, float fontSize) throws DocumentException, IOException {
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	return Utilities.pointsToMillimeters(bf.getWidthPoint(str, fontSize));
    }
    
    public void addTextAt(String text, float x, float y) throws DocumentException, IOException {
    	canvas.saveState();
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	canvas.beginText();
    	canvas.moveText(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.setFontAndSize(bf, 8);
    	canvas.showText(text);
    	canvas.endText();
    	canvas.restoreState();
    }
    
    public void addTextAt(String text, float fontSize, float x, float y) throws DocumentException, IOException {
    	canvas.saveState();
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	canvas.beginText();
    	canvas.moveText(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.setFontAndSize(bf, fontSize);
    	canvas.showText(text);
    	canvas.endText();
    	canvas.restoreState();
    }
    
    public void addTextAt(String text, float fontSize, float rotation, float x, float y) throws DocumentException, IOException {
    	canvas.saveState();
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	canvas.beginText();
    	canvas.setFontAndSize(bf, fontSize);
    	canvas.showTextAligned(PdfContentByte.ALIGN_LEFT, text, Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y), rotation);
    	canvas.endText();
    	canvas.restoreState();
    }
    
    public void addImageAt(Image image, float x, float y) throws DocumentException {
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.addImage(image);
    }
    
    public void addImageAt(Image image, float x, float y, float w, float h) throws DocumentException {
    	image.scaleAbsolute(Utilities.millimetersToPoints(w), Utilities.millimetersToPoints(h));
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.addImage(image);
    }
    
    public Image cropImage(Image image, float x1, float y1, float x2, float y2) throws DocumentException
    {
    	float origWidth = image.getScaledWidth();
        float origHeight = image.getScaledHeight();
        PdfTemplate t = canvas.createTemplate(Utilities.millimetersToPoints(x2),Utilities.millimetersToPoints(y2));
        t.addImage(image, origWidth, 0, 0, origHeight, Utilities.millimetersToPoints(-x1), Utilities.millimetersToPoints(-y1));
        return Image.getInstance(t);
    }
    
    public void addImageRotatedAt(Image image, float x, float y, float w, float h, float rotation) throws DocumentException {
    	image.scaleAbsolute(Utilities.millimetersToPoints(w), Utilities.millimetersToPoints(h));
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	image.setRotationDegrees(rotation);
    	canvas.addImage(image);
    }
    
    public void addImageAt(Image image, float x, float y, float w, float h, float scale) throws DocumentException {
    	image.scaleAbsolute(Utilities.millimetersToPoints(w), Utilities.millimetersToPoints(h));
    	image.scalePercent(scale);
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.addImage(image);
    }
    
    public void addLineAt(float lineWidth, float x1, float y1, float x2, float y2) {
    	canvas.saveState();
    	canvas.setLineWidth(Utilities.millimetersToPoints(lineWidth));
    	canvas.moveTo(Utilities.millimetersToPoints(x1), Utilities.millimetersToPoints(y1));
    	canvas.lineTo(Utilities.millimetersToPoints(x2), Utilities.millimetersToPoints(y2));
    	canvas.setRGBColorStroke(255, 0, 0);
    	canvas.stroke();
    	canvas.restoreState();
    }
    
    public void addLineAt(float lineWidth, float x1, float y1, float x2, float y2, int r, int g, int b) {
    	canvas.saveState();
    	canvas.setLineWidth(Utilities.millimetersToPoints(lineWidth));
    	canvas.moveTo(Utilities.millimetersToPoints(x1), Utilities.millimetersToPoints(y1));
    	canvas.lineTo(Utilities.millimetersToPoints(x2), Utilities.millimetersToPoints(y2));
    	canvas.setRGBColorStroke(r, g, b);
    	canvas.stroke();
    	canvas.restoreState();
    }
    
    public void addNewPage() {
    	document.newPage();
    }
    
    public void addNewPage(int r, int g, int b) {
    	document.newPage();
    	setBackgroundColour(r, g, b);
    }
    
    public Document getDocument() {
    	return document;
    }
    
    public void close() {
    	document.close();
    	System.gc();
    }
    
    public static class CompressedPdf implements StreamSource {

    	private final InputStream is;
    	
    	public CompressedPdf(Pdf pdf) throws IOException, DocumentException {
    		is = manipulatePdf(pdf.getStream());
    	}
    	
    	private static ByteArrayInputStream manipulatePdf(InputStream input) throws IOException, DocumentException {
            //PdfName key = new PdfName("ITXT_SpecialId");
            //PdfName value = new PdfName("123456789");
            ByteArrayOutputStream output = new ByteArrayOutputStream();
            // Read the file
            PdfReader reader = new PdfReader(input);
            int n = reader.getXrefSize();
            PdfObject object;
            PRStream stream;
            // Look for image and manipulate image stream
            for (int i = 0; i < n; i++) {
                object = reader.getPdfObject(i);
                if (object == null || !object.isStream())
                    continue;
                stream = (PRStream)object;
               // if (value.equals(stream.get(key))) {
                PdfObject pdfsubtype = stream.get(PdfName.SUBTYPE);
                if (pdfsubtype != null && pdfsubtype.toString().equals(PdfName.IMAGE.toString())) {
                    PdfImageObject image = new PdfImageObject(stream);
                    BufferedImage bi = image.getBufferedImage();
                    if (bi == null) continue;
                    int width = (int)(bi.getWidth() * 1.0f);
                    int height = (int)(bi.getHeight() * 1.0f);
                    BufferedImage img = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);
                    AffineTransform at = AffineTransform.getScaleInstance(1.0f, 1.0f);
                    Graphics2D g = img.createGraphics();
                    g.drawRenderedImage(bi, at);
                    ByteArrayOutputStream imgBytes = new ByteArrayOutputStream();
                    ImageIO.write(img, "JPG", imgBytes);
                    stream.clear();
                    stream.setData(imgBytes.toByteArray(), false, PRStream.BEST_COMPRESSION);
                    stream.put(PdfName.TYPE, PdfName.XOBJECT);
                    stream.put(PdfName.SUBTYPE, PdfName.IMAGE);
                    //stream.put(key, value);
                    stream.put(PdfName.FILTER, PdfName.DCTDECODE);
                    stream.put(PdfName.WIDTH, new PdfNumber(width));
                    stream.put(PdfName.HEIGHT, new PdfNumber(height));
                    stream.put(PdfName.BITSPERCOMPONENT, new PdfNumber(8));
                    stream.put(PdfName.COLORSPACE, PdfName.DEVICERGB);
                }
            }
            // Save altered PDF
            PdfStamper stamper = new PdfStamper(reader, output);
            stamper.close();
            reader.close();
            return new ByteArrayInputStream(output.toByteArray());
        }
    	
		@Override
		public InputStream getStream() {
			return is;
		}
    	
    }
    
    
    

    @Override
    public InputStream getStream() {
        // Here we return the pdf contents as a byte-array
        return new ByteArrayInputStream(os.toByteArray());
    }
}