import React from 'react';
import { BlockMath, InlineMath } from 'react-katex';
import 'katex/dist/katex.min.css';

interface MathTextRendererProps {
    content: string;
    display?: boolean;
    className?: string;
}

/**
 * Component to render text with support for:
 * - Math equations (LaTeX) in $$...$$ (block) or $...$ (inline)
 * - Bangla text (automatically rendered with Bengali font)
 * - Regular text
 */
export const MathTextRenderer: React.FC<MathTextRendererProps> = ({ 
    content, 
    display = false,
    className = ''
}) => {
    if (!content) return null;

    // Split by block math ($$...$$)
    const blockMathRegex = /\$\$([\s\S]*?)\$\$/g;
    const parts: React.ReactNode[] = [];
    let lastIndex = 0;
    let match;

    // Process block math ($$...$$)
    while ((match = blockMathRegex.exec(content)) !== null) {
        // Add text before math
        if (match.index > lastIndex) {
            const textBefore = content.substring(lastIndex, match.index);
            parts.push(...renderInlineContent(textBefore));
        }
        
        // Add block math
        try {
            parts.push(<BlockMath key={parts.length} math={match[1].trim()} />);
        } catch (e) {
            // If math rendering fails, show original text
            parts.push(<span key={parts.length} className="text-destructive">$${match[1]}$$</span>);
        }
        
        lastIndex = match.index + match[0].length;
    }

    // Add remaining text
    if (lastIndex < content.length) {
        parts.push(...renderInlineContent(content.substring(lastIndex)));
    }

    // If no block math was found, process inline math and text
    if (parts.length === 0) {
        parts.push(...renderInlineContent(content));
    }

    return <span className={className}>{parts}</span>;
};

/**
 * Renders content with inline math ($...$) and Bangla text
 */
function renderInlineContent(text: string): React.ReactNode[] {
    const parts: React.ReactNode[] = [];
    const inlineMathRegex = /(?<!\$)\$([^$\n]+)\$(?!\$)/g;
    let lastIndex = 0;
    let match;

    while ((match = inlineMathRegex.exec(text)) !== null) {
        // Add text before inline math
        if (match.index > lastIndex) {
            const textBefore = text.substring(lastIndex, match.index);
            parts.push(
                <span key={parts.length} style={{ fontFamily: 'var(--font-bengali)' }}>
                    {textBefore}
                </span>
            );
        }
        
        // Add inline math
        try {
            parts.push(<InlineMath key={parts.length} math={match[1].trim()} />);
        } catch (e) {
            // If math rendering fails, show original text
            parts.push(<span key={parts.length} className="text-destructive">${match[1]}$</span>);
        }
        
        lastIndex = match.index + match[0].length;
    }

    // Add remaining text
    if (lastIndex < text.length) {
        const remainingText = text.substring(lastIndex);
        parts.push(
            <span key={parts.length} style={{ fontFamily: 'var(--font-bengali)' }}>
                {remainingText}
            </span>
        );
    }

    // If no inline math was found, just render the text
    if (parts.length === 0) {
        parts.push(
            <span key={0} style={{ fontFamily: 'var(--font-bengali)' }}>
                {text}
            </span>
        );
    }

    return parts;
}
