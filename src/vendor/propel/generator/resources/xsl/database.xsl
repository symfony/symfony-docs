<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE xsl:stylesheet [<!ENTITY nbsp "&#160;">]>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!--
	XSL Stylesheet to normalize a database schema
	-->

	<!--
	Output indented UTF 8 XML 
	-->
	<xsl:output method="xml" indent="yes" encoding="UTF-8" />

	<!--
	Matches root database node, the only allowed root node

	Starts the normalization process
	-->
	<xsl:template match='/database'>
		<database>
			<xsl:if test='not(boolean(@defaultIdMethod))'>
				<xsl:attribute name='defaultIdMethod'>native</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@defaultPhpNamingMethod))'>
				<xsl:attribute name='defaultPhpNamingMethod'>underscore</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@heavyIndexing))'>
				<xsl:attribute name='heavyIndexing'>false</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='external-schema'/>
			<xsl:apply-templates select='table'/>
			<xsl:apply-templates select='behavior'/>
		</database>
	</xsl:template>

	<!--
	Normalizes any defaultPhpNamingMethod attribute by making it lowercase
	-->	
	<xsl:template match='@defaultPhPNamingMethod'>
		<xsl:attribute name='defaultPhPNamingMethod'><xsl:value-of select='translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")'/></xsl:attribute>
	</xsl:template>

	<!--
	Normalizes any onDelete attribute by making it lowercase, or none if it is empty (makes onDelete='' act the same as onDelete='none')
	-->	
	<xsl:template match='@onDelete' name='onDelete'>
		<xsl:choose>
			<xsl:when test='.=""'>
				<xsl:attribute name='onDelete'>none</xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:attribute name='onDelete'><xsl:value-of select='translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")'/></xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!--
	Handle OnDelete the same as onDelete
	-->
	<xsl:template match='@OnDelete'>
		<xsl:call-template name='onDelete'/>
	</xsl:template>

	<!--
	Normalizes any onUpdate attribute by making it lowercase, or none if it is empty (similar to onDelete)
	-->	
	<xsl:template match='@onUpdate' name='onUpdate'>
		<xsl:choose>
			<xsl:when test='.=""'>
				<xsl:attribute name='onUpdate'>none</xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:attribute name='onUpdate'><xsl:value-of select='translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")'/></xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!--
	Handle OnUpdate the same as onUpdate
	-->
	<xsl:template match='@OnUpdate'>
		<xsl:call-template name='onUpdate'/>
	</xsl:template>

	<!--
	Tranlate IdMethod attribute to idMethod attribute
	-->
	<xsl:template match='@IdMethod'>
		<xsl:attribute name='idMethod'><xsl:value-of select='.'/></xsl:attribute>
	</xsl:template>

	<!--
	Just copy any attribute
	-->
	<xsl:template match='@*' priority='-1'>
		<xsl:copy-of select='.'/>
	</xsl:template>

	<!--
	Normalize a table, add some attribute with default values if ommitted and normalize all attribute and childnodes
	-->
	<xsl:template match='table'>
		<table>
			<xsl:if test='not(boolean(@skipSql))'>
				<xsl:attribute name='skipSql'>false</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@abstract))'>
				<xsl:attribute name='abstract'>false</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='column'/>
			<xsl:apply-templates select='foreign-key'/>
			<xsl:apply-templates select='index'/>
			<xsl:apply-templates select='unique'/>
			<xsl:apply-templates select='id-method-parameter'/>
			<xsl:apply-templates select='validator'/>
			<xsl:apply-templates select='vendor'/>
			<xsl:apply-templates select='behavior'/>
		</table>
	</xsl:template>

	<!--
	Normalize a foreign-key, add some attribute with default values if ommitted and normalize all attribute and childnodes
	-->
	<xsl:template match='foreign-key'>
		<foreign-key>
			<xsl:if test='not(boolean(@onDelete)) and not(boolean(@OnDelete))'>
				<xsl:attribute name='onDelete'>none</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@onUpdate)) and not(boolean(@OnUpdate))'>
				<xsl:attribute name='onUpdate'>none</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='reference'/>
			<xsl:apply-templates select='vendor'/>
		</foreign-key>
	</xsl:template>

	<!--
	Just copy the index node with attributes and add the index-column
	-->
	<xsl:template match='index'>
		<index>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='index-column'/>
		</index>
	</xsl:template>

	<!--
	Just copy the unique node with attributes and add the unique-column
	-->
	<xsl:template match='unique'>
		<unique>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='unique-column'/>
		</unique>
	</xsl:template>

	<!--
	Just copy the behavior node with attributes and add the param
	-->
	<xsl:template match='behavior'>
		<behavior>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='parameter'/>
		</behavior>
	</xsl:template>


	<!--
	Just copy the unique-column node with attributes and add the vendor node
	-->
	<xsl:template match='unique-column'>
		<unique-column>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='vendor'/>
		</unique-column>
	</xsl:template>

	<!--
	Just copy the index-column node with attributes and add the vendor node
	-->
	<xsl:template match='index-column'>
		<index-column>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='vendor'/>
		</index-column>
	</xsl:template>

	<!--
	Add default name to id-method-parameter (if none) and copy its attributes
	-->
	<xsl:template match='id-method-parameter'>
		<id-method-parameter>
			<xsl:if test='not(boolean(@name))'>
				<xsl:attribute name='name'>default</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select='@*'/>
		</id-method-parameter>
	</xsl:template>

	<!--
	Just copy the validator node with attributes and add the rule node
	-->
	<xsl:template match='validator'>
		<validator>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='rule'/>
		</validator>
	</xsl:template>

	<!--
	Adds a default name to the rule (if none given) and copy the attributes
	-->
	<xsl:template match='rule'>
		<rule>
			<xsl:if test='not(boolean(@name))'>
				<xsl:attribute name='name'>class</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select='@*'/>
		</rule>
	</xsl:template>

	<!--
	Strip all childnodes (if any) from a parameter node
	-->
	<xsl:template match='parameter'>
		<parameter>
			<xsl:apply-templates select='@*'/>
		</parameter>
	</xsl:template>

	<!--
	Just copy the vendor node with attributes and add the parameter node
	-->
	<xsl:template match='vendor'>
		<vendor>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='parameter'/>
		</vendor>
	</xsl:template>

	<!--
	Strip all childnodes from an inheritance node
	-->
	<xsl:template match='inheritance'>
		<inheritance>
			<xsl:apply-templates select='@*'/>
		</inheritance>
	</xsl:template>

	<!--
	Normalize a column node, add default values for missing attributes and copy the content
	-->
	<xsl:template match='column'>
		<column>
			<xsl:if test='not(boolean(@primaryKey))'>
				<xsl:attribute name='primaryKey'>false</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@required))'>
				<xsl:attribute name='required'>false</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@type))'>
				<xsl:attribute name='type'>VARCHAR</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@autoIncrement))'>
				<xsl:attribute name='autoIncrement'>false</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@lazyLoad))'>
				<xsl:attribute name='lazyLoad'>false</xsl:attribute>
			</xsl:if>
			<xsl:if test='@type = "VARCHAR" and not(boolean(@sqlType)) and not(boolean(@size))'>
				<xsl:attribute name='size'>255</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='inheritance'/>
			<xsl:apply-templates select='vendor'/>
		</column>
	</xsl:template>

	<!--
	Strip all childnodes from an reference node
	-->
	<xsl:template match='reference'>
		<reference>
			<xsl:apply-templates select='@*'/>
		</reference>
	</xsl:template>

</xsl:stylesheet>
