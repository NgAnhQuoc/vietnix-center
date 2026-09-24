<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">

<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>

<!-- Template to format datetime -->
<xsl:template name="format-date">
    <xsl:param name="date"/>
    
    <xsl:if test="$date">
        <!-- Extract date parts from: 2025-11-21T10:30:00+00:00 -->
        <xsl:variable name="datepart" select="substring-before($date, 'T')"/>
        <xsl:variable name="timepart" select="substring-after($date, 'T')"/>
        
        <!-- Extract time (HH:MM) -->
        <xsl:variable name="time" select="substring($timepart, 1, 5)"/>
        
        <!-- Extract timezone (handle both +00:00 and Z format) -->
        <xsl:variable name="tz">
            <xsl:choose>
                <xsl:when test="contains($timepart, '+')">
                    <xsl:variable name="tzfull" select="substring-after($timepart, '+')"/>
                    <xsl:value-of select="concat('+', substring($tzfull, 1, 2), ':', substring($tzfull, 4, 2))"/>
                </xsl:when>
                <xsl:when test="contains($timepart, '-') and string-length(substring-after($timepart, '-')) = 5">
                    <xsl:variable name="tzfull" select="substring-after($timepart, '-')"/>
                    <xsl:value-of select="concat('-', substring($tzfull, 1, 2), ':', substring($tzfull, 4, 2))"/>
                </xsl:when>
                <xsl:when test="contains($timepart, 'Z')">
                    <xsl:text>+00:00</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>+00:00</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <!-- Format: 2019-10-02 06:16 +07:00 -->
        <xsl:value-of select="$datepart"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="$time"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="$tz"/>
    </xsl:if>
</xsl:template>

<xsl:template match="/">
<html lang="vi">
<head>
    <title>VNX Sitemap</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px 20px;
            margin: 20px 30px;
            border-radius: 4px;
        }
        
        .info-box strong {
            color: #2196F3;
        }
        .info-box p{
            margin-top: 8px;
        }
        .table-container {
            padding: 30px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #667eea;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .url-cell {
            color: #667eea;
            text-decoration: none;
            word-break: break-all;
            display: block;
            max-width: 500px;
        }
        
        .url-cell:hover {
            text-decoration: underline;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            background: #28a745;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.images {
            background: #17a2b8;
        }
        
        .badge.video {
            background: #dc3545;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        /* Sitemap Index Styles */
        .sitemap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 30px;
        }
        
        .sitemap-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .sitemap-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        
        .sitemap-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .sitemap-card a {
            color: #667eea;
            text-decoration: none;
            word-break: break-all;
            font-size: 14px;
        }
        
        .sitemap-meta {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            font-size: 13px;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 24px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                padding: 15px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 10px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>VNX XML Sitemap</h1>
            <p>Generated by Vietnix Custom Sitemap Plugin</p>
        </div>
        
        <xsl:choose>
            <!-- Sitemap Index -->
            <xsl:when test="sitemap:sitemapindex">
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number">
                            <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/>
                        </div>
                        <div class="stat-label">Sitemaps</div>
                    </div>
                </div>
                
                <div class="info-box">
                    <strong>Sitemap Index:</strong> Đây là sitemap tổng hợp. Click vào từng sitemap bên dưới để xem chi tiết URLs.
                </div>
                
                <div class="sitemap-grid">
                    <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                        <div class="sitemap-card" onclick="window.location.href='{sitemap:loc}'">
                            <h3> Sitemap #<xsl:value-of select="position()"/></h3>
                            <a href="{sitemap:loc}">
                                <xsl:value-of select="sitemap:loc"/>
                            </a>
                            <div class="sitemap-meta">
                                <strong>Last Modified: </strong> 
                                <xsl:call-template name="format-date">
                                    <xsl:with-param name="date" select="sitemap:lastmod"/>
                                </xsl:call-template>
                            </div>
                        </div>
                    </xsl:for-each>
                </div>
            </xsl:when>
            
            <!-- URL Sitemap -->
            <xsl:otherwise>
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number">
                            <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/>
                        </div>
                        <div class="stat-label">URLs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">
                            <xsl:value-of select="count(sitemap:urlset/sitemap:url/image:image)"/>
                        </div>
                        <div class="stat-label">Images</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">
                            <xsl:value-of select="count(sitemap:urlset/sitemap:url/video:video)"/>
                        </div>
                        <div class="stat-label">Videos</div>
                    </div>
                </div>
                
                <div class="info-box">
                    <strong>XML Sitemap:</strong> Sitemap này chứa tất cả URLs được submit lên Google Search Console. 
                    <a href="https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview" target="_blank" style="color: #2196F3;">Tìm hiểu thêm</a>
                    <p><a href="/vnx_sitemap.xml">Xem vnx_sitemap.xml</a></p>

                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>URL</th>
                                <th style="width: 120px;">Images</th>
                                <th style="width: 120px;">Videos</th>
                                <th style="width: 220px;">Last Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sitemap:urlset/sitemap:url">
                                <tr>
                                    <td><xsl:value-of select="position()"/></td>
                                    <td>
                                        <a href="{sitemap:loc}" class="url-cell" target="_blank">
                                            <xsl:value-of select="sitemap:loc"/>
                                        </a>
                                    </td>
                                    <td>
                                        <xsl:choose>
                                            <xsl:when test="count(image:image) > 0">
                                                <span class="badge images">
                                                    <xsl:value-of select="count(image:image)"/> ảnh
                                                </span>
                                            </xsl:when>
                                            <xsl:otherwise>-</xsl:otherwise>
                                        </xsl:choose>
                                    </td>
                                    <td>
                                        <xsl:choose>
                                            <xsl:when test="count(video:video) > 0">
                                                <span class="badge video">
                                                    <xsl:value-of select="count(video:video)"/> video
                                                </span>
                                            </xsl:when>
                                            <xsl:otherwise>-</xsl:otherwise>
                                        </xsl:choose>
                                    </td>
                                    <td>
                                        <!-- Display formatted lastmod or video publication date -->
                                        <xsl:choose>
                                            <xsl:when test="sitemap:lastmod">
                                                <xsl:call-template name="format-date">
                                                    <xsl:with-param name="date" select="sitemap:lastmod"/>
                                                </xsl:call-template>
                                            </xsl:when>
                                            <xsl:when test="video:video/video:publication_date">
                                                <xsl:call-template name="format-date">
                                                    <xsl:with-param name="date" select="video:video/video:publication_date"/>
                                                </xsl:call-template>
                                            </xsl:when>
                                            <xsl:otherwise>-</xsl:otherwise>
                                        </xsl:choose>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </div>
            </xsl:otherwise>
        </xsl:choose>
        
        <div class="footer">
            Generated by <a href="https://vietnix.vn" target="_blank">Vietnix</a> Custom Sitemap
            | <a href="https://www.sitemaps.org/protocol.html" target="_blank">Sitemap Protocol</a>
            | <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a>
        </div>
    </div>
</body>
</html>
</xsl:template>

</xsl:stylesheet>
