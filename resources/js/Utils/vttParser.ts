/**
 * WebVTT Parser Utility for Synchronized Video Transcripts
 * Enterprise LMS standard: parses WebVTT cues with millisecond precision
 */

export interface VttCue {
    id: string;
    startTime: number; // in seconds
    endTime: number;   // in seconds
    text: string;      // cue subtitle text
}

/**
 * Parses time format: "00:01:23.456" or "01:23.456" into seconds (float)
 */
export function parseVttTimestamp(timestamp: string): number {
    const parts = timestamp.trim().split(':');
    let hours = 0;
    let minutes = 0;
    let secondsWithMs = 0;

    if (parts.length === 3) {
        hours = parseFloat(parts[0]);
        minutes = parseFloat(parts[1]);
        secondsWithMs = parseFloat(parts[2].replace(',', '.'));
    } else if (parts.length === 2) {
        minutes = parseFloat(parts[0]);
        secondsWithMs = parseFloat(parts[1].replace(',', '.'));
    }

    return (hours * 3600) + (minutes * 60) + secondsWithMs;
}

/**
 * Format seconds back into friendly MM:SS string
 */
export function formatVttTime(seconds: number): string {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

/**
 * Parses full WebVTT string content into structured VttCue array
 */
export function parseVttContent(vttText: string): VttCue[] {
    const cues: VttCue[] = [];
    if (!vttText) return cues;

    // Normalize newlines and split into cue blocks
    const lines = vttText.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
    let currentCue: Partial<VttCue> | null = null;
    let cueIdCounter = 1;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();

        // Skip WEBVTT header and comments
        if (!line || line.startsWith('WEBVTT') || line.startsWith('NOTE')) {
            if (currentCue && currentCue.startTime !== undefined && currentCue.text) {
                cues.push({
                    id: currentCue.id || `cue-${cueIdCounter++}`,
                    startTime: currentCue.startTime,
                    endTime: currentCue.endTime || currentCue.startTime + 2,
                    text: currentCue.text.trim(),
                });
                currentCue = null;
            }
            continue;
        }

        // Check for timestamp line (e.g. "00:00:01.000 --> 00:00:04.500")
        if (line.includes('-->')) {
            const [startPart, endPart] = line.split('-->');
            const endClean = endPart.trim().split(' ')[0]; // ignore positioning settings like align:start

            currentCue = {
                id: `cue-${cueIdCounter++}`,
                startTime: parseVttTimestamp(startPart),
                endTime: parseVttTimestamp(endClean),
                text: '',
            };
            continue;
        }

        // Text lines for the current cue
        if (currentCue) {
            // Strip WebVTT tags like <c.color>, <b>, <v Voice>
            const cleanLine = line.replace(/<[^>]*>/g, '');
            currentCue.text = currentCue.text ? `${currentCue.text} ${cleanLine}` : cleanLine;
        }
    }

    // Flush last cue if exists
    if (currentCue && currentCue.startTime !== undefined && currentCue.text) {
        cues.push({
            id: currentCue.id || `cue-${cueIdCounter++}`,
            startTime: currentCue.startTime,
            endTime: currentCue.endTime || currentCue.startTime + 2,
            text: currentCue.text.trim(),
        });
    }

    return cues;
}
